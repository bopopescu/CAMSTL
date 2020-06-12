// ATS FIXME: Rename application to "Network-Monitor"

#include <stdio.h>
#include <unistd.h>
#include <stdlib.h>
#include <string.h>
#include <errno.h>
#include <netdb.h>
#include <sys/wait.h>
#include <sys/select.h>
#include <sys/socket.h>
#include <sys/ioctl.h>
#include <linux/inotify.h>
#include <asm/types.h>
#include <linux/netlink.h>
#include <linux/rtnetlink.h>
#include <pthread.h>
#include <semaphore.h>
#include <netdb.h>
#include <net/if.h>
#include <netinet/in.h>
#include <arpa/inet.h>

#include "ats-common.h"
#include "ats-string.h"
#include "atslogger.h"
#include "ConfigDB.h"
#include "command_line_parser.h"
#include "timer-event.h"
#include "socket_interface.h"
#include "RedStone_IPC.h"

static ATSLogger g_log;
static const int g_init_pid = 1;
static const ats::String g_google_primary_dns_ip("8.8.8.8");

static const char* g_udhcpc = "/sbin/udhcpc";
static const char* g_dhcp_conf_src_fname = "/tmp/config/dhcpd.conf";
static const char* g_dhcp_conf_tmp_fname = "/tmp/config/dhcpd.conf.network-monitor";

static const char* g_resolv_conf = "/tmp/config/resolv.conf";

// AWARE360 FIXME: Is it necessary to include interface specific information in Network-Monitor?
static const ats::String g_wifi("ra0");
static const ats::String g_wifi_client("apcli0");
static bool g_wifi_running = false;
static bool g_wifi_client_running = false;
static bool g_wifi_client_ip_attached = false;

// Description: Stores global non-thread-safe information for managed interfaces.
static ats::StringMapMap g_iface;

// Description: Stores interface names. This global variable is only written once before
//	threading (concurrent processing) starts, and so therefore is thread safe.
static ats::StringMap g_iface_name;

class MyData;
REDSTONE_IPC g_RedStoneData;

typedef int (*NetlinkMessageHandler)(MyData&, struct nlmsghdr* p_msg);
typedef std::map <int, NetlinkMessageHandler> IntNetlinkHandlerMap;
typedef std::pair <int, NetlinkMessageHandler> IntNetlinkHandlerPair;
ats::String g_RouteOverride;
ats::String g_RouteIP;

// Description: Contains information about a network event.
//
// XXX: In the future if NetworkEvent becomes very complicated but must still be efficient, then
//	modify it to be a pure-virtual object that other network types override.
//
//	For now, due to the small number of event types and simplicity of the structures,
//	NetworkEvent just uses a union.
class NetworkEvent
{
public:
	static const int NEWLINK_EVENT = 0;
	static const int DELLINK_EVENT = 1;
	static const int NEWADDR_EVENT = 2;
	static const int DHCP_EVENT = 3;

	NetworkEvent(int p_type)
	{
		m_type = p_type;
	}

	int m_type;

	struct NewlinkEvent
	{
		char m_iface[IF_NAMESIZE];
		bool m_up; // If true, is an "up" event, otherwise it is a "down" event
	};

	struct DellinkEvent
	{
		char m_iface[IF_NAMESIZE];
	};

	struct NewaddrEvent
	{
		// AWARE360 FIXME: Perhaps new address information should be included in this event structure,
		//	rather than having the event generator inject it into the interface data structure?
		//	Either way seems fine at this point.
		char m_iface[IF_NAMESIZE];
		#if 0
		char m_ip[INET_ADDRSTRLEN];
		char m_netmask[INET_ADDRSTRLEN];
		char m_broadcast[INET_ADDRSTRLEN];
		#endif
	};

	struct DHCPEvent
	{
		char m_iface[IF_NAMESIZE];
		bool m_server; // If true, is a "server" event, otherwise it is a "client" event
	};

	union EventData
	{
		NewlinkEvent m_newlink;
		DellinkEvent m_dellink;
		NewaddrEvent m_newaddr;
		DHCPEvent m_dhcp;
	};

	EventData m_data;
};

typedef std::vector<NetworkEvent*> EventList;

// Description: Deletes all network event objects in "p_elist". The "p_elist" object must
//	be destroyed (no longer used) after this call.
//
//	XXX: For efficiency, this function does not re-initialize the "p_elist" object to
//	a "good" state upon return, so the caller shall no loger use the object.
static void delete_event_list(const EventList& p_elist)
{

	for(size_t i = 0; i < p_elist.size(); ++i)
	{
		delete p_elist[i];
	}

}

class LeaseExpireAlarm;
typedef std::map <const ats::String, LeaseExpireAlarm*> LeaseExpireAlarmMap;
typedef std::pair <const ats::String, LeaseExpireAlarm*> LeaseExpireAlarmPair;

class MyData : public ats::CommonData
{
public:
	MyData();

	// Description: Keeps track of all Lease-Expire-Alarms running for each managed interface.
	//
	//	LeaseExpireAlarm objects are tracked by interface name. If an interface name exists
	//	in "m_lea", then it means that it has a DHCP lease that is being monitored for expiry.
	//	Otherwise the interface has no DHCP lease to monitor.
	LeaseExpireAlarmMap m_lea;

	IntNetlinkHandlerMap m_netlink;

	// Description: Contains the name of the interface hosting the system DNS server
	//	(this is the DNS server which appears in "/etc/resolv.conf").
	ats::String m_dns_interface;

	ats::String m_default_resolv_conf;

	pthread_t m_netlink_thread;
	pthread_t m_dhcpd_watch_thread;
	int m_netlink_fd;

	// Description: Will be incremented to 1 when the netlink monitoring system is ready.
	//	The current state of the network is undefined until this semaphore is incremented
	//	to 1.
	sem_t m_netlink_monitor_ready_sem;

	// Description: Blocks until the netlink monitoring system is ready. The network state is
	//	undefined until this function returns.
	void wait_for_netlink_monitor_to_be_ready()
	{
		sem_wait(&m_netlink_monitor_ready_sem);
	}

	// Description: Stores a list of network events as they occurred in order.
	EventList m_network_event;

	// Description: Will be incremented to 1 if one or more network events have occurred.
	//	In other words, it lets listeners know that there are pending network events
	//	to examine/process.
	//
	//	The listener can retrieve all network events so far by calling "wait_for_network_event".
	sem_t m_network_event_sem;

	// Description: Posts the network event "p_event", where "p_event" is a pointer to a NetworkEvent
	//	object created with "new", or "p_event" must be NULL. This function shall become the owner
	//	of object "p_event" and will call "delete" on it (if "p_event" is not NULL) once it has
	//	been acknowledged.
	//
	//	NULL can be passed in for "p_event" to cause the NetworkMonitor system to update itself,
	//	even though there is no specific network event to update.
	//
	// XXX: Shall only be called with "lock_data" held.
	void post_network_event(NetworkEvent* p_event)
	{

		if(p_event)
		{
			const char *type = "";

			switch(p_event->m_type)
			{
			case NetworkEvent::NEWLINK_EVENT: type = "NEWLINK_EVENT"; break;
			case NetworkEvent::DELLINK_EVENT: type = "DELLINK_EVENT"; break;
			case NetworkEvent::NEWADDR_EVENT: type = "NEWADDR_EVENT"; break;
			case NetworkEvent::DHCP_EVENT: type = "DHCP_EVENT"; break;
			}

			ats_logf(ATSLOG_INFO, "POST_NETWORK_EVENT: type=%s", type);
			m_network_event.push_back(p_event);
		}

		if(set("link-msg", "", false))
		{
			sem_post(&(m_network_event_sem));
		}

	}

	// Description: Blocks until a network event occurs if "p_block" is true. If "p_block" is false, then
	//	this function returns immediately and "p_elist" will have the new network events appended to
	//	it, or will be unchanged if there are no new network events at the time.
	//
	//	When the function returns, "p_elist" will be appended with all the new network events that
	//	occurred. The caller must call "delete_event_list" on "p_elist" after use to free resources.
	void wait_for_network_event(EventList& p_elist, bool p_block=true)
	{

		if(!p_block)
		{

			if(sem_trywait(&m_network_event_sem))
			{
				return;
			}

		}
		else
		{	
			sem_wait(&m_network_event_sem);
		}

		// XXX: A "post_network_event" could occur at this point, which will increment semaphore "m_network_event_sem".
		//	Instead of waiting for this new semaphore post to be processed on the next call to "wait_for_network_event",
		//	the function "sem_trywait" should be called (within a held "lock_data") until the semaphore "m_network_event_sem"
		//	has been decremented to zero. Then all network events can be passed to the caller in this single
		//	"wait_for_network_event" call.

		lock_data();

		// XXX: Collapse (throw-away) all superfluous semaphore "posts" since the single semaphore "post" which just occurred
		//	was sufficient to allow this function to pass ALL current network events to the caller.
		while(!sem_trywait(&m_network_event_sem));

		if(p_elist.empty())
		{
			p_elist = m_network_event;
		}
		else
		{
			p_elist.reserve(p_elist.size() + m_network_event.size());

			for(size_t i = 0; i < m_network_event.size(); ++i)
			{
				p_elist.push_back(m_network_event[i]);
			}

		}

		m_network_event.clear();
		unset("link-msg");
		unlock_data();
	}

	void generate_initial_dhcp_conf_file();

	ats::String m_initial_dhcpd_conf;

	// ATS FIXME: Create a class/object to store interfaces, rather than having numerous global variables
	//	named by interfaces. This will reduce all variables per interface to a single class/object.
	//	This will also simplify function calls and the entire program in general.

private:
	MyData(const MyData&);
	MyData& operator =(const MyData&);
};

MyData::MyData()
{
	sem_init(&m_network_event_sem, 0, 0);
	sem_init(&m_netlink_monitor_ready_sem, 0, 0);
}

void MyData::generate_initial_dhcp_conf_file()
{
	const int ret = ats::read_file(g_dhcp_conf_src_fname, m_initial_dhcpd_conf);

	if(ret < 0)
	{
		ats_logf(ATSLOG_INFO, "%s: read_file(\"%s\") failed, returned %d", __FUNCTION__, g_dhcp_conf_src_fname, ret);
		exit(1);
	}

	FILE* f = fopen(g_dhcp_conf_src_fname, "w");

	if(!f)
	{
		ats_logf(ATSLOG_INFO, "%s: Could not open \"%s\" for writing, cannot start dhcpd", __FUNCTION__, g_dhcp_conf_src_fname);
		exit(1);
	}

	fwrite(m_initial_dhcpd_conf.c_str(), 1, m_initial_dhcpd_conf.length(), f);
	fclose(f);
}

static int g_out_pipe[2];
static int g_err_pipe[2];
static pthread_t g_thread;

// Description: Watches this programs stdout and stderr and redirects all messages to ats_logf.
//
//	This allows users to log output/error messages from the "udhcpc", "dhcpd", and other child processes.
//
//	Messages from stdout are prefixed with "[OUT]" and stderr are prefixed with "[ERR]". Any non-line-printable
//	characters encountered in the output/error stream are escaped as hex using "\xXX".
//
//	NOTE: Internal program ats_logf, process stdout and stderr line messages will all be interleaved randomly.
//	However each line appearing in the log file will be complete in its entirety. It is the responsibility of
//	the log file reviewer to decipher the mishmash.
//
// XXX: It is possible to track each output by process (pid) by simply creating a pipe-per-process, but it is not
//	worth it at this point.
//
// Return: A NULL pointer is returned on error, and this function does not return otherwise.
static void* h_stdout_thread(void*)
{

	for(;;)
	{
		fd_set rfds;
		FD_ZERO(&rfds);
		FD_SET(g_out_pipe[0], &rfds);
		FD_SET(g_err_pipe[0], &rfds);

		const int max_fd = ((g_out_pipe[0] > g_err_pipe[0]) ? g_out_pipe[0] : g_err_pipe[0]);
		const int ret = select(max_fd + 1, &rfds, NULL, NULL, NULL);

		if(-1 == ret)
		{
			ats_logf(ATSLOG_INFO, "STDIO:: %s: select failed: (%d) %s", __FUNCTION__, errno, strerror(errno));
			break;
		}

		char buf[1024];
		int fd;
		const char* tag;

		if(FD_ISSET(g_out_pipe[0], &rfds))
		{
			fd = g_out_pipe[0];
			tag = "[OUT]";
		}
		else
		{
			fd = g_err_pipe[0];
			tag = "[ERR]";
		}

		const ssize_t nread = read(fd, buf, sizeof(buf));

		if(nread <= 0)
		{

			if((nread < 0) && (EINTR == errno))
			{
				continue;
			}
	
			ats_logf(ATSLOG_INFO, "STDIO::%s: (%d) %s", __FUNCTION__, errno, strerror(errno));
			break;
		}

		ats_logf(ATSLOG_INFO, "STDIO:: %s %s", tag, ats::to_line_printable(ats::String(buf, nread)).c_str());
	}

	close(g_out_pipe[0]);
	close(g_err_pipe[0]);
	return 0;
}

static int g_dhcpd_pid = 0;

// Description: Watches and waits for the "dhcpd" server to terminate.
static void* watch_dhcpd(void* p)
{
	MyData& md = *((MyData*)p);
	int status;
	waitpid(g_dhcpd_pid, &status, 0);
	md.lock_data();
	ats_logf(ATSLOG_INFO, "POSTING DHCP NETWORK EVENT: %d", __LINE__);
	NetworkEvent* ne = new NetworkEvent(NetworkEvent::DHCP_EVENT);
	ne->m_data.m_dhcp.m_iface[0] = '\0';
	ne->m_data.m_dhcp.m_server = true;
	md.post_network_event(ne);
	md.unlock_data();

	if(WIFEXITED(status))
	{
		const int code = WEXITSTATUS(status);
		ats_logf(ATSLOG_INFO, "%s: dhcpd exited with code %d", __FUNCTION__, code);

		if(!code)
		{
			return 0;
		}

	}
	else if(WIFSIGNALED(status))
	{
		const int termsig = WTERMSIG(status);
		ats_logf(ATSLOG_INFO, "%s: dhcpd exited with signal %d", __FUNCTION__, termsig);

		if(SIGTERM == termsig)
		{
			return 0;
		}

	}
	else
	{
		ats_logf(ATSLOG_INFO, "%s: dhcpd exited with status 0x%08X", __FUNCTION__, status);
	}

	// XXX: If "dhcpd" terminated abnormally, then a one second sleep will be enforced to
	//	prevent from using up all the CPU, since it is likely that a future call will
	//	fail again (the termination is abnormal, and so there is no pre-defined
	//	recovery mechanism).
	sleep(1);
	return 0;
}

// Description: Displays an error message about "pthread_create" failing, terminates the
//	application and returns an error code of 1.
//
// XXX: "pthread_create" failing (which should never happen) is always fatal.
static void check_for_pthread_create_fail(int p_errno, const char* p_fn, int p_line)
{

	if(p_errno)
	{
		ats_logf(ATSLOG_INFO, "%s,%d: pthread_create failed: (%d) %s", p_fn, p_line, p_errno, strerror(p_errno));
		exit(1);
	}

}

// Description: Starts the "dhcpd" server and if successful, starts a thread that watches
//	and waits for the "dhcpd" server to terminate.
//
//	Nothing is done if a "dhcpd" server is already running.
static void start_dhcpd(MyData& p_md)
{
	ats_logf(ATSLOG_INFO, "Starting dhcpd server...");
	int pid = p_md.get_int("dhcpd_pid");

	if(pid > g_init_pid)
	{
		return;
	}

	if(!(pid = fork()))
	{
		const char* g_dhcpd = "/usr/sbin/dhcpd";
		execl(g_dhcpd, g_dhcpd, "-f", (char*)NULL);
		exit(1);
	}

	p_md.set("dhcpd_pid", ats::toStr(g_dhcpd_pid = pid));

	check_for_pthread_create_fail(pthread_create(&(p_md.m_dhcpd_watch_thread), 0, watch_dhcpd, &p_md), __FUNCTION__, __LINE__);
}

// Description: Stops the "dhcpd" server and terminates the thread watching the sever.
//
//	Nothing is done if there is no "dhcpd" server running.
static void stop_dhcpd(MyData& p_md)
{
	ats_logf(ATSLOG_INFO, "Stopping dhcpd server...");
	const int pid = p_md.get_int("dhcpd_pid");

	if(pid > g_init_pid)
	{
		kill(pid, SIGTERM);
		pthread_join(p_md.m_dhcpd_watch_thread, 0);
		p_md.unset("dhcpd_pid");
	}

}

// Description: Appends "dhcpd.conf" entry "p_entry" to the end of the "dhcpd.conf" file.
//
//	"p_is_default" is simply used for logging, indicating that "p_entry" is a program default entry or an
//	entry from a previous "dhcpd.conf" file.
static void add_entry_to_dhcpd_conf(MyData& p_md, const ats::String& p_iface, const ats::String& p_entry, bool p_is_default)
{
	FILE* f = fopen(g_dhcp_conf_src_fname, "r");

	if(!f)
	{
		return;
	}

	FILE* des = fopen(g_dhcp_conf_tmp_fname, "w");

	if(!des)
	{
		fclose(f);
		return;
	}

	ats::ReadDataCache_FILE rdc(f);

	for(;;)
	{
		ats::String p_line;
		const int ret = ats::get_file_line(p_line, rdc, 1);

		if(ret < 0)
		{
			break;
		}

		fwrite(p_line.c_str(), 1, p_line.length(), des);
		fwrite("\n", 1, 1, des);
	}

	fclose(f);

	if(p_is_default)
	{
		ats_logf(ATSLOG_INFO, "%s: Using default %s dhcpd.conf entry", __FUNCTION__, p_iface.c_str());
	}
	else
	{
		ats_logf(ATSLOG_INFO, "%s: Using %s dhcpd.conf entry loaded from file", __FUNCTION__, p_iface.c_str());
	}

	fwrite(p_entry.c_str(), 1, p_entry.length(), des);
	fclose(des);
	rename(g_dhcp_conf_tmp_fname, g_dhcp_conf_src_fname);
	{
		ats::String entry;
		ats::prefix_lines(ats::PrefixContext_String(entry), p_entry, "\t");
		ats_logf(ATSLOG_INFO, "DHCP::  Added %s%s to DHCP conf file:\n\t%s", p_is_default ? "default " : "", p_iface.c_str(), entry.c_str());
	}
}

// Description: Removes the DHCP interface (subnet) defined in the "dhcpd.conf" file, where the
//	entry is marked by "p_tag".
//
//	If the tagged entry is found, then it is removed from the "dhcpd.conf" file and stored
//	in "p_dhcp_entry". "p_is_default" will be set to "false", indicating that the DHCP
//	entry passed into the function has been modified. The caller shall set "p_is_default" to
//	true before the very first call of this function (and leave the variable untouched for
//	subsequent calls to this function).
//
//	A "dhcpd.conf" entry is delimited by a start tag line "p_tag" and a terminating "}" line.
static void remove_entry_from_dhcpd_conf(const ats::String& p_tag, ats::String& p_dhcp_entry, bool& p_is_default)
{
	FILE* f = fopen(g_dhcp_conf_src_fname, "r");

	if(!f)
	{
		return;
	}

	FILE* des = fopen(g_dhcp_conf_tmp_fname, "w");

	if(!des)
	{
		fclose(f);
		return;
	}

	ats::ReadDataCache_FILE rdc(f);

	for(;;)
	{
		ats::String p_line;
		const int ret = ats::get_file_line(p_line, rdc, 1);

		if(ret < 0)
		{
			break;
		}

		if(0 == strcasecmp(p_line.c_str(), p_tag.c_str()))
		{
			p_is_default = false;
			p_dhcp_entry = p_line;
			p_dhcp_entry += '\n';

			for(;;)
			{
				const int ret = ats::get_file_line(p_line, rdc, 1);

				if(ret < 0)
				{
					break;
				}

				p_dhcp_entry += p_line;
				p_dhcp_entry += '\n';

				if("}" == p_line)
				{
					break;
				}

			}

			continue;
		}

		fwrite(p_line.c_str(), 1, p_line.length(), des);
		fwrite("\n", 1, 1, des);
	}

	fclose(f);
	fclose(des);
	rename(g_dhcp_conf_tmp_fname, g_dhcp_conf_src_fname);
}

static void remove_iface_from_dhcpd_conf(
	const ats::String& p_display_name,
	const ats::String& p_entry_tag,
	ats::String& p_dhcpd_conf,
	bool& p_is_default_dhcpd_conf)
{
	ats_logf(ATSLOG_INFO, "Removing %s entry from \"dhcpd.conf\"...", p_display_name.c_str());
	{
		ats::String s;
		ats::prefix_lines(ats::PrefixContext_String(s), p_dhcpd_conf, "\t");
		ats_logf(ATSLOG_INFO, "DHCP::  Removing %s entry \"%s\" from DHCP conf:\n\t%s", p_display_name.c_str(), p_entry_tag.c_str(), s.c_str());
	}
	remove_entry_from_dhcpd_conf(p_entry_tag, p_dhcpd_conf, p_is_default_dhcpd_conf);
}

static void set_interface_ip_netmask_bcast(const ats::String& p_iface, const ats::String& p_ip, const ats::String& p_netmask, const ats::String& p_bcast)
{
	struct ifreq ifr;
	const int fd = socket(PF_INET, SOCK_DGRAM, IPPROTO_IP);

	if(fd < 0)
	{
		ats_logf(ATSLOG_INFO, "%s: Failed to retore IP on interface %s", __FUNCTION__, p_iface.c_str());
	}
	else
	{
		strncpy(ifr.ifr_name, p_iface.c_str(), IFNAMSIZ);
		ifr.ifr_addr.sa_family = AF_INET;

		if(!p_ip.empty())
		{
			inet_pton(AF_INET, p_ip.c_str(), ifr.ifr_addr.sa_data + 2);
			ioctl(fd, SIOCSIFADDR, &ifr);
		}

		if(!(p_netmask.empty()))
		{
			inet_pton(AF_INET, p_netmask.c_str(), ifr.ifr_addr.sa_data + 2);
 			ioctl(fd, SIOCSIFNETMASK, &ifr);
		}

		if(!(p_bcast.empty()))
		{
			inet_pton(AF_INET, p_bcast.c_str(), ifr.ifr_addr.sa_data + 2);
			ioctl(fd, SIOCSIFBRDADDR, &ifr);
		}

		close(fd);
	}

}

class LeaseExpireAlarm
{
public:
	LeaseExpireAlarm(MyData& p_md, int p_time)
	{
		m_md = &p_md;
		m_time = p_time;
	}

	LeaseExpireAlarmMap::iterator m_i;
	ats::TimerEvent m_timer;
	pthread_t m_thread;
	MyData* m_md;
	int m_time;
};

void* lease_expire_alarm(void* p)
{
	LeaseExpireAlarm& lea = *((LeaseExpireAlarm*)p);

	if(0 == lea.m_timer.start_timer_and_wait(lea.m_time, 0))
	{

		if(!lea.m_timer.m_reason.is_cancelled())
		{
			NetworkEvent* ne = new NetworkEvent(NetworkEvent::DHCP_EVENT);
			strncpy(ne->m_data.m_dhcp.m_iface, (lea.m_i->first).c_str(), sizeof(ne->m_data.m_dhcp.m_iface) - 1);
			ne->m_data.m_dhcp.m_iface[sizeof(ne->m_data.m_dhcp.m_iface) - 1] = '\0';
			ne->m_data.m_dhcp.m_server = false;
			ats_logf(ATSLOG_INFO, "%s: POSTING DHCP NETWORK EVENT: %d, iface=%s, time=%d", __FUNCTION__, __LINE__, (lea.m_i->first).c_str(), lea.m_time);
			lea.m_md->post_network_event(ne);
		}

	}

	return 0;
}

// Description: Lets Network-Monitor know that interface "p_iface" can provide DNS resolv_conf settings.
//
//	Note that Network-Monitor will not immediately use the DNS resolv_conf settings registered, and
//	instead will select the DNS resolv_conf with the lowest metric value (highest metric priority)
//	when actually modifying the system DNS resolv_conf.
//
//	XXX: The current implementation does not actually remember all DNS resolv_conf settings for each
//	     interface. It instead only stores the one with the lowest metrc value (highest metric priority).
//	     This is OK, because it still meets the callers expectations as specified in the description
//	     above.
//
//	     If Network-Monitor ever needs to drop to a lower priority metric (higher metric value), then it
//	     will re-process all interface DNS resolv_conf registrations.
static void register_dns_resolv_conf_for_this_interface(MyData& p_md, const ats::String& p_iface)
{
	p_md.m_dns_interface = p_iface;
	const ats::StringMap& m = g_iface.get(p_iface);
	const int metric = m.get_int("metric");

	// Do not update DNS (resolv_conf) if an interface with a higher priority metric (lower metric value)
	// has already written to the file.
	if(
		(!(p_md.m_dns_interface.empty()))
		&&
		((g_iface.get(p_md.m_dns_interface)).get_int("metric") < metric))
	{
		return;
	}

}

// Description: Modifies the system DNS resolv_conf with the current highest metric priority (lowest metric value)
//	interface DNS resolv_conf registered. If there is no DNS resolv_conf information registered for any
//	interface, then nothing is done.
static void apply_dns_resolv_conf(MyData& p_md)
{
	const ats::String& iface = p_md.m_dns_interface;

	if(iface.empty())
	{
		return;
	}

	const ats::StringMap& m = g_iface.get(iface);
	const ats::String& resolv_conf_fname = m.get("resolv_conf");

	if(resolv_conf_fname.empty())
	{
		return;
	}

	const int metric = m.get_int("metric");
	ats_logf(ATSLOG_INFO, "%s: Interface %s is updating DNS resolv_conf with metric %d", __FUNCTION__, iface.c_str(), metric);

	const int pid = fork();

	if(!pid)
	{
		const char* cp = "/bin/cp";
		execl(cp, cp, resolv_conf_fname.c_str(), "/tmp/config/resolv.conf", (char*)NULL);
		exit(1);
	}

	int status;
	waitpid(pid, &status, 0);

	if(WIFEXITED(status))
	{
		const int exit_status = WEXITSTATUS(status);

		if(0 != exit_status)
		{
			ats_logf(ATSLOG_INFO, "%s: cp exited with %d: Failed to cp \"%s\" to \"%s\"", __FUNCTION__, exit_status, resolv_conf_fname.c_str(), "/tmp/config/resolv.conf");
		}

	}
	else
	{
		ats_logf(ATSLOG_INFO, "%s: cp exit status 0x%08X: Failed to cp \"%s\" to \"%s\"", __FUNCTION__, status, resolv_conf_fname.c_str(), "/tmp/config/resolv.conf");
	}

}

// Description: Requests IP address for interface "p_iface" from a DHCP server (if any).
//
//	NOTE: This function will unset the IP address set on interface "p_iface" so that it can perform the DHCP queries.
//
//	If a DHCP server is found, and a valid IP address was received, then the interface will be updated to use that
//	new IP address.
//
//	If no DHCP server is found, then the interface will be set to the IP address information from the system
//	configuration database. Each value that cannot be retrieved from the system configuration database will be
//	set to the value passed in (i.e. "p_ip", "p_netmask" and "p_bcast").
//
// XXX: The udhcpc call in this function causes udhcpc to terminate on success or failure, so multiple instances of
//	this process are not a concern (if network-monitor is restarted). The multiple processes will exist for a
//	short time, and will not adversely affect the system while they exist concurrently.
//
// Return: True is returned if a DHCP IP address was aquired, and false is returned otherwise.
static bool get_dhcp_ip_from_interface(MyData& p_md, const ats::String& p_iface, const ats::String& p_ip, const ats::String& p_netmask, const ats::String& p_bcast)
{
	p_md.lock_data();
	LeaseExpireAlarmMap::iterator i = p_md.m_lea.find(p_iface);

	if(i != p_md.m_lea.end())
	{
		LeaseExpireAlarm* lea = i->second;
		const bool cancel = true;
		const bool disable = true;
		lea->m_timer.stop_timer(cancel, disable);
		pthread_join(lea->m_thread, 0);
		delete lea;
		p_md.m_lea.erase(i);
	}

	p_md.unlock_data();

	ats::StringMap& m = g_iface.get(p_iface);
	const ats::String& resolv_conf_fname = "/tmp/.network-monitor-" + p_iface + "-resolv.conf";
	m.set("resolv_conf", resolv_conf_fname);

	int p[2];
	pipe(p);

	const int pid = fork();

	if(!pid)
	{
		close(p[0]);
		dup2(p[1], STDOUT_FILENO);
		setenv("NETWORK_MONITOR", (ats::toStr(getpid())).c_str(), 1);
		setenv("metric", (m.get("metric")).c_str(), 1);
		setenv("RESOLV_CONF", resolv_conf_fname.c_str(), 1);
		execl(g_udhcpc, g_udhcpc, "-i", p_iface.c_str(), "-n", "-f", "-q", "-A", "3", "-t", "3", (char*)NULL);
		exit(1);
	}

	close(p[1]);
	ats::ReadDataCache_fd rdc(p[0]);

	const ats::String lease_line(" obtained, lease time ");
	ats::String line;
	int lease_expire_time = -1;

	for(;;)
	{
		const int c = rdc.getc();

		if(c < 0)
		{
			break;
		}

		if('\n' != c)
		{
			line += c;
			continue;
		}

		ats_logf(ATSLOG_INFO, "udhcpc(%s): %s", p_iface.c_str(), line.c_str());
		const char* s = strstr(line.c_str(), lease_line.c_str());

		if(s)
		{
			lease_expire_time = strtol(s + lease_line.length(), 0, 0);
			ats_logf(ATSLOG_INFO, "LEASE EXPIRES IN: %d", lease_expire_time);
		}

		line.clear();
	}

	close(p[0]);
	int status;
	waitpid(pid, &status, 0);

	if(WIFEXITED(status) && (0 == WEXITSTATUS(status)))
	{

		if(lease_expire_time >= 0)
		{
			p_md.lock_data();
			LeaseExpireAlarm* lea = new LeaseExpireAlarm(p_md, lease_expire_time);
			lea->m_i = (p_md.m_lea.insert(LeaseExpireAlarmPair(p_iface, lea))).first;
			const int ret = pthread_create(&(lea->m_thread), 0, lease_expire_alarm, lea);

			if(ret)
			{
				p_md.m_lea.erase(lea->m_i);
				delete lea;
				ats_logf(ATSLOG_INFO, "%s(%s): Failed to create lease_expire_alarm thread (%d) %s", __FUNCTION__, p_iface.c_str(), errno, strerror(errno));
			}

			p_md.unlock_data();
		}

		register_dns_resolv_conf_for_this_interface(p_md, p_iface);
		return true;
	}

	     	ats::StringList addr;
		{
			db_monitor::ConfigDB db;
			// ATS FIXME: The "<p_iface>addr" key should contain "<IP>,<Netmask>,<Broadcast>", however at time of
			//	writing it only contains "<IP>". This could cause the interface to be set with an undesirable
			//	netmask or broadcast address.
			ats::split(addr, db.GetValue("system", p_iface + "addr"), ",");
		}

		const ats::String& ip = (addr.size() >= 1) ? addr[0] : p_ip;
		const ats::String& netmask = (addr.size() >= 2) ? addr[1] : p_netmask;
		const ats::String& bcast = (addr.size() >= 3) ? addr[2] : p_bcast;
		set_interface_ip_netmask_bcast(p_iface, ip, netmask, bcast);
		ats_logf(ATSLOG_INFO, "%s: udhcpc failed, restored \"%s\": %s, nmask(%s), bcast(%s)", __FUNCTION__, p_iface.c_str(), p_ip.c_str(), p_netmask.c_str(), p_bcast.c_str());
		return false;
}

// Description: Returns true if interface "p_iface" is ready for dhcpd monitoring.
//
//	An interface must at least be "up and running" and have a valid IP address to be
//	considered ready for dhcpd monitoring.
//
// Return: True is returned if interface is ready for dhcpd monitoring, and false is
//	returned otherwise.
static bool is_interface_dhcpd_ready(MyData& p_md, const ats::String& p_iface, ats::String& p_ip, ats::String& p_netmask, ats::String& p_bcast)
{
	const ats::String& ip_key = p_iface + "_ip";
	const ats::String& netmask_key = p_iface + "_netmask";
	const ats::String& bcast_key = p_iface + "_bcast";
	p_md.lock_data();
	const ats::String& up = p_md.get(p_iface);
	p_ip = p_md.get(ip_key);
	p_netmask = p_md.get(netmask_key);
	p_bcast = p_md.get(bcast_key);
	p_md.unlock_data();
	return (("up" == up) && (!(p_ip.empty())));
}

// Description: Returns true if flags "p_flags" indicate that the interface is up and running.
//
//	NOTE: "up and running" does not mean cable, wireless or other physical connection. It
//	just means that the interface is allocated resources by the Kernel, and is in an "operational"
//	state.
//
// Return: True if the interface is up and running, and false is returned otherwise.
static bool interface_is_up_and_running(int p_flags)
{
	return ((IFF_RUNNING | IFF_UP) == ((IFF_RUNNING | IFF_UP) & p_flags));
}

// Description: Parses netlink events, processing relevant events and ignoring
//	all others.
static int parse_message(MyData& p_md, int len, char* p_buf)
{ 
	struct nlmsghdr* msg_ptr;

	for
	(
		msg_ptr = (struct nlmsghdr*)p_buf;
		NLMSG_OK(msg_ptr, (unsigned int)len);
		msg_ptr = NLMSG_NEXT(msg_ptr, len)
	)
	{

		if(msg_ptr->nlmsg_type == NLMSG_DONE)
		{
			ats_logf(ATSLOG_INFO, "read_netlink: NLMSG_DONE");
			break;
		}

		if(msg_ptr->nlmsg_type == NLMSG_ERROR)
		{
			ats_logf(ATSLOG_INFO, "read_netlink: Message is an error - decode TBD");
			break;
		}

		IntNetlinkHandlerMap::const_iterator i = p_md.m_netlink.find(msg_ptr->nlmsg_type);

		if(i != p_md.m_netlink.end())
		{
			i->second(p_md, msg_ptr);
		}

	}

	return 0;
}

// Description: Wrapper around the "if_indextoname" function. Returns the name of the interface
//	referenced by "p_ifi".
//
// Return: The name of the interface referenced in "p_ifi" is returned on success, and an empty string
//	is returned on error. The reason for the error is from "if_indextoname" and will be set in
//	"errno".
static ats::String get_ifname(struct ifinfomsg* p_ifi)
{
	char ifname[IF_NAMESIZE + 1];

	if(!if_indextoname(p_ifi->ifi_index, ifname))
	{
		return ats::g_empty;
	}

	ifname[sizeof(ifname) - 1] = '\0';
	return ifname;
}

// Description: Helper function that gets interface (IP, netmask or broadcast) address information, and logs errors.
//
//	XXX: If this function fails with "EADDRNOTAVAIL   99      /* Cannot assign requested address */", it means that
//	     the requested address does not exist for that interface at that time, and could not be assigned to the
//	     "ifreq p_ifr" structure.
//
// Return: The interface address is returned, otherwise the empty string is returned.
static const char* get_interface_addr_by_ioctl(int p_fd, int p_request, struct ifreq& p_ifr, const ats::String& p_iface)
{
	const char* request_name;

	switch(p_request)
	{
	case SIOCGIFADDR: request_name = "SIOCGIFADDR"; break;
	case SIOCGIFNETMASK: request_name = "SIOCGIFNETMASK"; break;
	case SIOCGIFBRDADDR: request_name = "SIOCGIFBRDADDR"; break;
	default: return "";
	}

	if(ioctl(p_fd, p_request, &p_ifr) < 0)
	{
		ats_logf(ATSLOG_INFO, "%s: %s(%s) (%d) %s", __FUNCTION__, request_name, p_iface.c_str(), errno, strerror(errno));
		return "";
	}

	return inet_ntoa(((struct sockaddr_in*)&(p_ifr.ifr_addr))->sin_addr);
}

// Description: Retrieves interface information for "p_iface". "p_ip", "p_netmask" and "p_bcast", if not NULL, must be set
//	to the empty string before this call.
static void get_interface_information(const ats::String& p_iface, ats::String* p_ip, ats::String* p_netmask, ats::String* p_bcast)
{
	const int fd = socket(PF_INET, SOCK_DGRAM, IPPROTO_IP);

	if(fd >= 0)
	{
		struct ifreq ifr;
		strncpy(ifr.ifr_name, p_iface.c_str(), IFNAMSIZ - 1);
		ifr.ifr_name[IFNAMSIZ - 1] = '\0';

		if(p_ip)
		{
			*p_ip = get_interface_addr_by_ioctl(fd, SIOCGIFNETMASK, ifr, p_iface);
		}

		if(p_netmask)
		{
			*p_netmask = get_interface_addr_by_ioctl(fd, SIOCGIFNETMASK, ifr, p_iface);
		}

		if(p_bcast)
		{
			*p_bcast = get_interface_addr_by_ioctl(fd, SIOCGIFBRDADDR, ifr, p_iface);
		}

		close(fd);
	}

}

static bool delete_default_route(const ats::String& p_iface)
{
	const int pid = fork();

	if(!pid)
	{
		const char* ip = "/sbin/ip";
		execl(ip, ip, "route", "del", "default", "dev", p_iface.c_str(), (char*)NULL);
		exit(1);
	}

	int status;
	waitpid(pid, &status, 0);

	if(WIFEXITED(status) && (0 == WEXITSTATUS(status)))
	{
		return true;
	}

	return false;
}

// AWARE360 FIXME: Why is "eth0" assumed to be the default route?
// Override the default route when the unit is set up for secondary routing.
// It should always just report to the primary TRULink located at eth0.RouteIP
// The primary TRULink will do normal routing there.
// Dave Huff Nov 2014
static bool set_secondary_route(MyData& p_md)
{
	ats_logf(ATSLOG_INFO, "set_secondary_route");
	char primaryIP[32];
	const ats::String& pIP = p_md.get("eth0_ip");

	if(pIP.length() > 0)  // do we have an eth0 IP
	{
		ats_logf(ATSLOG_INFO, "eth0 IP is %s", pIP.c_str());
		strcpy(primaryIP, pIP.c_str());
		char *p = strrchr(primaryIP, '.');

		if(p)
		{
			*(++p) = '\0';
		
			strcat(primaryIP,	g_RouteIP.c_str());
			ats_logf(ATSLOG_INFO, "Adding secondary route to route table %s", primaryIP);

			char cmdBuf[128];
			sprintf(cmdBuf, "/sbin/route add default gw %s", primaryIP);
			system(cmdBuf);
		}

	}

	return true;
}

// Description: Call-back for when an interface is brought up.
//
//	The interface is set to "up" in the MyData database. Note however that no IP address, netmask,
//	or broadcast address is set for the interface. These address are set in the "on_netlink_newaddr"
//	call-back.
//
// Return: 0 is returned on success and a negative errno number is returned on error.
static int on_netlink_newlink(MyData& p_md, struct nlmsghdr* p_msg)
{
	struct ifinfomsg* ifi = (struct ifinfomsg*)(NLMSG_DATA(p_msg));
	ats::String ifname(get_ifname(ifi));

	if(ifname.empty())
	{
		ats_logf(ATSLOG_INFO, "%s: if_indextoname: (%d) %s", __FUNCTION__, errno, strerror(errno));
		return -errno;
	}

	if(!(g_iface_name.has_key(ifname)))
	{
		return 0;
	}

	if(interface_is_up_and_running(ifi->ifi_flags))
	{
		// XXX: Must always read interface IP, netmask and broadcast information on a "newlink"
		//	because netlink sometimes combines "newlink" and "newaddr" events. For a "true"
		//	"newlink" event, IP, netmask and/or broadcast may not be available (this is OK,
		//	and expected).
		ats::String ip;
		ats::String netmask;
		ats::String bcast;
		get_interface_information(ifname, &ip, &netmask, &bcast);

		p_md.lock_data();

		if((p_md.get(ifname) != "up"))
		{
			const ats::String& ip_key = ifname + ats::String("_ip");
			const ats::String& netmask_key = ifname + ats::String("_netmask");
			const ats::String& bcast_key = ifname + ats::String("_bcast");
			p_md.set(ip_key, ip);
			p_md.set(netmask_key, netmask);
			p_md.set(bcast_key, bcast);
			NetworkEvent* ne = new NetworkEvent(NetworkEvent::NEWLINK_EVENT);
			strncpy(ne->m_data.m_newlink.m_iface, ifname.c_str(), sizeof(ne->m_data.m_newlink.m_iface) - 1);
			ne->m_data.m_newlink.m_iface[sizeof(ne->m_data.m_newlink.m_iface) - 1] = '\0';
			ne->m_data.m_newlink.m_up = true;
			p_md.post_network_event(ne);
		}

		p_md.set(ifname, "up");
		p_md.unlock_data();
		ats_logf(ATSLOG_INFO, "RTM_NEWLINK: %s link up", ifname.c_str());

		// AWARE360 FIXME: Interface (3rd-party application) specific information should not be here.
		//	Remove code or create a generic callback/registration system so that Network-Monitor will be
		//	portable and scalable.

		if(ifname == g_wifi)
		{
			g_wifi_running = true;
		}
		else if(ifname == g_wifi_client)
		{
			g_wifi_client_running = true;
		}

	}
	else
	{
		p_md.lock_data();

		if(!(p_md.get(ifname).empty()))
		{
			NetworkEvent* ne = new NetworkEvent(NetworkEvent::NEWLINK_EVENT);
			strncpy(ne->m_data.m_newlink.m_iface, ifname.c_str(), sizeof(ne->m_data.m_newlink.m_iface) - 1);
			ne->m_data.m_newlink.m_iface[sizeof(ne->m_data.m_newlink.m_iface) - 1] = '\0';
			ne->m_data.m_newlink.m_up = false;
			p_md.post_network_event(ne);
		}

		p_md.unset(ifname);
		p_md.unlock_data();
		ats_logf(ATSLOG_INFO, "%s: RTM_NEWLINK: %s link not up(%s) and running(%s) (is \"down\", flags=0x%08X)",
			__FUNCTION__,
			ifname.c_str(),
			(ifi->ifi_flags & IFF_UP) ? "true" : "false",
			(ifi->ifi_flags & IFF_RUNNING) ? "true" : "false",
			ifi->ifi_flags);
		const bool success = delete_default_route(ifname);
		ats_logf(ATSLOG_INFO, "%s: Deleted default route for \"%s\": %s", __FUNCTION__, ifname.c_str(), success ? "OK" : "FAILED");
	}

	return 0;
}

// Description: Call-back for when an interface is brought down.
//
//	The interface state is set to "down" (or not "up") in the MyData database.
//
// Return: 0 is returned on success and a negative errno number is returned on error.
static int on_netlink_dellink(MyData& p_md, struct nlmsghdr* p_msg)
{
	struct ifinfomsg* ifi = (struct ifinfomsg*)(NLMSG_DATA(p_msg));
	ats::String ifname(get_ifname(ifi));

	if(ifname.empty())
	{
		ats_logf(ATSLOG_INFO, "%s: if_indextoname: (%d) %s", __FUNCTION__, errno, strerror(errno));
		return -errno;
	}

	if(!(g_iface_name.has_key(ifname)))
	{
		return 0;
	}

	p_md.lock_data();

	if(!(p_md.get(ifname).empty()))
	{
		NetworkEvent* ne = new NetworkEvent(NetworkEvent::DELLINK_EVENT);
		strncpy(ne->m_data.m_dellink.m_iface, ifname.c_str(), sizeof(ne->m_data.m_dellink.m_iface) - 1);
		ne->m_data.m_dellink.m_iface[sizeof(ne->m_data.m_dellink.m_iface) - 1] = '\0';
		p_md.post_network_event(ne);
	}

	p_md.unset(ifname);
	p_md.unlock_data();
	ats_logf(ATSLOG_INFO, "%s: RTM_DELLINK: %s link down", __FUNCTION__, ifname.c_str());

	// AWARE360 FIXME: Interface (3rd-party application) specific information should not be here.
	//	Remove code or create a generic callback/registration system so that Network-Monitor will be
	//	portable and scalable.
	if(ifname == g_wifi)
	{
		g_wifi_running = false;
	}

	if(ifname == g_wifi_client)
	{
		g_wifi_client_running = false;
		g_wifi_client_ip_attached = false;
	}

	return 0;
}

// Description: Helper function for "on_netlink_newaddr" (does the actual address processing).
static void process_newaddr(MyData& p_md, uint32_t p_ipaddr, const char* p_name)
{
	ats_logf(ATSLOG_INFO, "%s is now %d.%d.%d.%d\n",
		p_name,
		(p_ipaddr >> 24) & 0xff,
		(p_ipaddr >> 16) & 0xff,
		(p_ipaddr >> 8) & 0xff,
		p_ipaddr & 0xff);

	ats::String ip;
	ats_sprintf(&ip, "%d.%d.%d.%d",
		(p_ipaddr >> 24) & 0xff,
		(p_ipaddr >> 16) & 0xff,
		(p_ipaddr >> 8) & 0xff,
		p_ipaddr & 0xff);

	ats::String netmask;
	ats::String bcast;
	get_interface_information(p_name, 0, &netmask, &bcast);

	p_md.lock_data();
	const ats::String& ip_key = p_name + ats::String("_ip");

	if(p_md.get(ip_key) != ip)
	{
		const ats::String& netmask_key = p_name + ats::String("_netmask");
		const ats::String& bcast_key = p_name + ats::String("_bcast");
		p_md.set(ip_key, ip);
		p_md.set(netmask_key, netmask);
		p_md.set(bcast_key, bcast);
		NetworkEvent* ne = new NetworkEvent(NetworkEvent::NEWADDR_EVENT);
		strncpy(ne->m_data.m_newaddr.m_iface, p_name, sizeof(ne->m_data.m_newaddr.m_iface) - 1);
		ne->m_data.m_newaddr.m_iface[sizeof(ne->m_data.m_newaddr.m_iface) - 1] = '\0';
		p_md.post_network_event(ne);
	}

	if(p_name == g_wifi_client)
	{
		g_RedStoneData.wifiLEDStatus(2);
		g_wifi_client_ip_attached = true;
	}

	p_md.unlock_data();
}

// Description: Call-back function for when an interface's IP address changes.
//
//	The IP address, netmask and broadcast address for the interface are updated in the
//	MyData database.
//
// Return: 0 is returned on success and a negative errno number is returned on error.
static int on_netlink_newaddr(MyData& p_md, struct nlmsghdr* p_msg)
{
	struct ifaddrmsg* ifa = (struct ifaddrmsg*)(NLMSG_DATA(p_msg));
	struct rtattr *rth = IFA_RTA(ifa);
	int rtl = IFA_PAYLOAD(p_msg);

	while(rtl && RTA_OK(rth, rtl))
	{

		if(IFA_LOCAL == rth->rta_type)
		{
			uint32_t ipaddr = htonl(*((uint32_t *)RTA_DATA(rth)));
			char name[IFNAMSIZ];
			if_indextoname(ifa->ifa_index, name);

			if(g_iface_name.has_key(name))
			{
				process_newaddr(p_md, ipaddr, name);
			}

		}

		rth = RTA_NEXT(rth, rtl);
	}

	return 0;
}

// Description: Gets the initial "up and running" status, IP, netmask and broadcast address
//	of interface "p_iface" and stores the results in the "MyData" database.
//
//	This function shall be called after connecting to netlink monitoring, but before processing
//	any netlink messages.
//
// Return: 0 is returned on success and a negative number is returned on error.
static int get_initial_interface_state(MyData& p_md, const ats::String& p_iface)
{
	p_md.lock_data();
	p_md.unset(p_iface);
	p_md.unlock_data();

	const int fd = socket(PF_INET, SOCK_DGRAM, IPPROTO_IP);

	if(fd < 0)
	{
		ats_logf(ATSLOG_INFO, "%s: Could not query initial %s state, interface is down", __FUNCTION__, p_iface.c_str());
		return -1;
	}

	struct ifreq ifr;
	strncpy(ifr.ifr_name, p_iface.c_str(), IFNAMSIZ - 1);
	ifr.ifr_name[IFNAMSIZ - 1] = '\0';
	ifr.ifr_flags = IFF_UP | IFF_RUNNING;

	if(ioctl(fd, SIOCGIFFLAGS, &ifr) < 0)
	{
		ats_logf(ATSLOG_INFO, "%s: ioctl failed to query \"%s\" (%d) %s, interface is down as far as this process(%d) is concerned", __FUNCTION__, p_iface.c_str(), errno, strerror(errno), getpid());
		close(fd);
		return -1;
	}

	const bool running = (IFF_UP | IFF_RUNNING) == (ifr.ifr_flags & (IFF_UP | IFF_RUNNING));
	const ats::String& ip = running ? get_interface_addr_by_ioctl(fd, SIOCGIFADDR, ifr, p_iface) : "";
	const ats::String& netmask = running ? get_interface_addr_by_ioctl(fd, SIOCGIFNETMASK, ifr, p_iface) : "";
	const ats::String& bcast = running ? get_interface_addr_by_ioctl(fd, SIOCGIFBRDADDR, ifr, p_iface) : "";

	close(fd);
	p_md.lock_data();

	if(running)
	{
		p_md.set(p_iface, "up");

		if(ip.empty())
		{
			ats_logf(ATSLOG_INFO, "Initial %s state is up, no IP", p_iface.c_str());
		}
		else
		{
			p_md.set(p_iface + "_ip", ip);
			p_md.set(p_iface + "_netmask", netmask);
			p_md.set(p_iface + "_bcast", bcast);
			ats_logf(ATSLOG_INFO, "Initial %s state is up, with IP=\"%s\"", p_iface.c_str(), ip.c_str());
		}

	}
	else
	{
		ats_logf(ATSLOG_INFO, "Initial %s state is down", p_iface.c_str());
	}

	p_md.unlock_data();
	return 0;
}

static int connect_to_netlink(MyData& p_md)
{
	p_md.m_netlink.insert(IntNetlinkHandlerPair(RTM_NEWLINK, on_netlink_newlink));
	p_md.m_netlink.insert(IntNetlinkHandlerPair(RTM_DELLINK, on_netlink_dellink));
	p_md.m_netlink.insert(IntNetlinkHandlerPair(RTM_NEWADDR, on_netlink_newaddr));

	p_md.m_netlink_fd = socket(AF_NETLINK, SOCK_DGRAM, NETLINK_ROUTE);
	struct sockaddr_nl local;
	memset(&local, 0, sizeof(local));
	local.nl_family = AF_NETLINK;
	local.nl_groups = RTMGRP_LINK | RTMGRP_IPV4_IFADDR;

	if(bind(p_md.m_netlink_fd, (struct sockaddr*)&local, sizeof(local)) < 0)
	{
		ats_logf(ATSLOG_INFO, "%s: (%d) %s", __FUNCTION__, errno, strerror(errno));
		return -1;
	}

	ats::StringMapMap::const_iterator i = g_iface.begin();

	while(i != g_iface.end())
	{
		const ats::String& iface = i->first;
		++i;
		get_initial_interface_state(p_md, iface);
	}

	sem_post(&(p_md.m_netlink_monitor_ready_sem));
	return 0;
}

static void process_netlink_messages(MyData& p_md)
{
	const size_t max_message_size = 4096;
	char buf[max_message_size];
	struct iovec iov = {buf, sizeof(buf)};
	struct sockaddr_nl snl;
	struct msghdr msg =
	{
		(void*)&snl,
		sizeof(snl),
		&iov,
		1,
		NULL,
		0,
		0
	};

	for(;;)
	{
		const int len = recvmsg(p_md.m_netlink_fd, &msg, 0);

		if(len <= 0)
		{
			ats_logf(ATSLOG_INFO, "%s: netlink connection terminated: (%d) %s", __FUNCTION__, errno, strerror(errno));
			break;
		}

		parse_message(p_md, len, buf);
	}

	close(p_md.m_netlink_fd);
}

static void* netlink_monitor(void* p)
{
	MyData& md = *((MyData*)p);

	if(0 != connect_to_netlink(md))
	{
		ats_logf(ATSLOG_INFO, "%s: Failed to connect to netlink", __FUNCTION__);
		exit(1);
	}

	process_netlink_messages(md);
	return 0;
}

// Description: Prepares the interface "p_iface" to run as a DHCP server or DHCP client. "p_mode" determines
//	how this function decides which DHCP mode the interface will use.
//
//	Valid modes are "auto" (let this function automatically choose either "server" or "client" mode),
//	"on" (run the interface as a DHCP server unconditionally), and "off" (run the interface as a
//	DHCP client unconditionally).
static bool configure_interface_for_dhcp(
	MyData& p_md,
	const ats::String& p_iface,
	const ats::String& p_mode,
	const ats::String& p_iface_description,
	const ats::String& p_iface_dhcp_entry,
	bool p_is_default_conf)
{
	ats::StringMap& m = g_iface.get(p_iface);
	bool run_dhcp = false;

	// AWARE360 FIXME: Changing ConfigDB settings for DHCP mode should also be a network event that causes the
	//	interface to NOT be processed. For now the user must restart NetworkMonitor if ConfigDB entires
	//	are modified.
	ats_logf(ATSLOG_INFO, "%d: state='%s', iface='%s'", __LINE__, (m.get("state")).c_str(), p_iface.c_str());

	if("processed" == m.get("state"))
	{
		// XXX: Interface already processed. Other than re-adding DHCP entires, and re-applying DNS resolv_conf, there is nothing to do.
		run_dhcp = m.get_bool("run_dhcp");

		if(run_dhcp)
		{
			add_entry_to_dhcpd_conf(p_md, p_iface_description, p_iface_dhcp_entry, p_is_default_conf);
			ats_logf(ATSLOG_INFO, "Running DHCP server on local %s (%s)", p_iface_description.c_str(), p_iface.c_str());
		}
 
		register_dns_resolv_conf_for_this_interface(p_md, p_iface);
		return run_dhcp;
	}

	ats::String ip;
	ats::String netmask;
	ats::String bcast;

	if("auto" == p_mode)
	{

		if(get_dhcp_ip_from_interface(p_md, p_iface, ip, netmask, bcast))
		{
			ats_logf(ATSLOG_INFO, "Foreign DHCP server detected on Ethernet, not running on Ethernet");
		}
		else
		{

			if(is_interface_dhcpd_ready(p_md, p_iface, ip, netmask, bcast))
			{
				run_dhcp = true;
				add_entry_to_dhcpd_conf(p_md, p_iface_description, p_iface_dhcp_entry, p_is_default_conf);
				ats_logf(ATSLOG_INFO, "Running DHCP server on local %s (%s)", p_iface_description.c_str(), p_iface.c_str());
			}

		}

	}
	else if("client" == p_mode)
	{
		get_dhcp_ip_from_interface(p_md, p_iface, ip, netmask, bcast);
	}
	else if(ats::get_bool(p_mode))
	{

		if(is_interface_dhcpd_ready(p_md, p_iface, ip, netmask, bcast))
		{
			run_dhcp = true;
			add_entry_to_dhcpd_conf(p_md, p_iface_description, p_iface_dhcp_entry, p_is_default_conf);
			ats_logf(ATSLOG_INFO, "Running DHCP server unconditionally on local %s (%s)", p_iface_description.c_str(), p_iface.c_str());
		}

	}
	else
	{
		get_dhcp_ip_from_interface(p_md, p_iface, ip, netmask, bcast);
		ats_logf(ATSLOG_INFO, "Running interface as a DHCP-Client unconditionally on interface %s (%s)", p_iface_description.c_str(), p_iface.c_str());
	}

	m.set("state", "processed");
	m.set("run_dhcp", run_dhcp ? "1" : "0");
	return run_dhcp;
}

// Description: Stops all dhcpd servers on the system.
//
// XXX: If "this" application (network-monitor) is killed, then its child process dhcpd server will
//	still be running, and thus prevent future instances of dhcpd from running. Therefore
//	any running dhcpd server must be killed.
static void stop_all_dhcpd_servers()
{
	ats_logf(ATSLOG_INFO, "Stopping all dhcpd servers");
	const int pid = fork();

	if(!pid)
	{
		const char* killall = "/usr/bin/killall";
		execl(killall, killall, "-9", "dhcpd", (char*)NULL);
		exit(1);
	}

	waitpid(pid, 0, 0);
}

static void use_default_resolv_conf(MyData& p_md)
{
	remove(g_resolv_conf);
	ats::write_file(g_resolv_conf, p_md.m_default_resolv_conf);
}

// Description:
//
//	1. Use default resolv.conf if one does not exist.
//	2. If an error during reading of resolv.conf occurs, use the default resolv.conf.
//	3. Scan current resolv.conf, allowing only 2 non-Google-primary DNS name servers.
//         If current resolv.conf is empty, then use the default resolv.conf.
//	4. Append Google's primary DNS server to resolv.conf if it does not exist, otherwise
//	   leave resolv.conf untouched.
static void make_sure_resolv_conf_has_primary_google_dns_server(MyData& p_md)
{
	FILE* f = fopen(g_resolv_conf, "r");

	if(!f)
	{
		use_default_resolv_conf(p_md);
		return;
	}

	ats::ReadDataCache_FILE rdc(f);
	ats::StringList sl;
	ats::String line;
	const ats::String nameserver("nameserver ");
	int ret = -ENODATA;

	int custom_nameserver_count = 0;
	bool has_google_dns = false;

	for(;;)
	{
		if((ret = get_file_line(line, rdc, 1)) < 0)
		{
			break;
		}

		if(0 == strncmp(nameserver.c_str(), line.c_str(), nameserver.length()))
		{
			const char* ip = line.c_str() + nameserver.length();

			if(0 != strncmp(g_google_primary_dns_ip.c_str(), ip, g_google_primary_dns_ip.length()))
			{

				if((custom_nameserver_count++) < 2)
				{
					sl.push_back(line);
				}

			}
			else
			{

				if(custom_nameserver_count < 3)
				{
					has_google_dns = true;
				}

				sl.push_back(line);
			}

		}
		else
		{
			sl.push_back(line);
		}

	}

	fclose(f);

	if((-ENODATA) != ret)
	{
		ats_logf(ATSLOG_INFO, "%s: Error while reading \"%s\": (%d) %s", __FUNCTION__, g_resolv_conf, ret, strerror(ret));
		use_default_resolv_conf(p_md);
		return;
	}

	if(!has_google_dns)
	{
		ats_logf(ATSLOG_INFO, "Current \"%s\" does not use primary Google DNS (%s), adding Google DNS...", g_resolv_conf, g_google_primary_dns_ip.c_str());
		sl.push_back(nameserver + g_google_primary_dns_ip);
		FILE* f = fopen(g_resolv_conf, "w");

		if(!f)
		{
			ats_logf(ATSLOG_INFO, "%s: Cannot open \"%s\" for writing: (%d) %s", __FUNCTION__, g_resolv_conf, errno, strerror(errno));
			return;
		}

		size_t i;

		for(i = 0; i < sl.size(); ++i)
		{
			const ats::String& s = sl[i];
			fwrite(s.c_str(), s.size(), 1, f);
			fprintf(f, "\n");
		}

		fclose(f);
	}

}

static int delete_route(const ats::StringList& p_sl)
{
	const int pid = fork();

	if(!pid)
	{
		char ip[] = "/sbin/ip";
		char route[] = "route";
		char add[] = "del";
		char** arg = new char*[p_sl.size() + 3];

		arg[0] = ip;
		arg[1] = route;
		arg[2] = add;
		size_t i;

		// First argument in "p_sl" is "dest" which is not used by the "ip" command, so
		// it is ignored.
		for(i = 1; i < p_sl.size(); ++i)
		{
			const int length = int(p_sl[i].length());
			char* s = new char[length + 1];
			strncpy(s, p_sl[i].c_str(), length);
			s[length] = '\0';
			arg[2 + i] = s;
		}

		arg[2 + i] = NULL;
		execv(ip, arg);
		exit(1);
	}

	int status;
	waitpid(pid, &status, 0);

	if(WIFEXITED(status))
	{
		return WEXITSTATUS(status);
	}

	return -1;
}

static int set_route(const ats::StringList& p_sl)
{
	const int pid = fork();

	if(!pid)
	{
		char ip[] = "/sbin/ip";
		char route[] = "route";
		char add[] = "add";
		char** arg = new char*[p_sl.size() + 3];

		arg[0] = ip;
		arg[1] = route;
		arg[2] = add;
		size_t i;

		// First argument in "p_sl" is "dest" which is not used by the "ip" command, so
		// it is ignored.
		for(i = 1; i < p_sl.size(); ++i)
		{
			const int length = int(p_sl[i].length());
			char* s = new char[length + 1];
			strncpy(s, p_sl[i].c_str(), length);
			s[length] = '\0';
			arg[2 + i] = s;
		}

		arg[2 + i] = NULL;
		execv(ip, arg);
		exit(1);
	}

	waitpid(pid, 0, 0);
	return 0;
}

static ats::String get_default_route(const ats::StringList& p_sl)
{

	if((p_sl.size() < 4) || (p_sl[0] != "dest") || (p_sl[1] != "default"))
	{
		return ats::String();
	}

	size_t i;

	for(i = 2; i < (p_sl.size() - 1); ++i)
	{

		if("dev" == p_sl[i])
		{
			return p_sl[i+1];
		}

	}

	return ats::String();
}

static int get_metric(const ats::StringList& p_sl)
{
	size_t i;

	for(i = 2; i < p_sl.size(); i += 2)
	{

		if("metric" == p_sl[i])
		{

			if(p_sl.size() >= (i+1))
			{
				return strtol(p_sl[i+1].c_str(), 0, 0);
			}

			return -1;
		}

	}

	return 0;
}

static void set_metric(ats::StringList& p_sl, int p_metric)
{
	const ats::String& metric = ats::toStr(p_metric);
	size_t i;

	for(i = 2; i < p_sl.size(); i += 2)
	{

		if("metric" == p_sl[i])
		{

			if(p_sl.size() >= (i+1))
			{
				p_sl[i + 1] = metric;
			}
			else
			{
				p_sl.push_back(metric);
			}

			return;
		}

	}

	p_sl.push_back("metric");
	p_sl.push_back(metric);
}

static bool white_space_only(const ats::String& p_s)
{
	size_t i;

	for(i = 0; i < p_s.size(); ++i)
	{

		switch(p_s[i])
		{
		case ' ':
		case '\n':
		case '\t':
		case '\r': break;
		default: return false;
		}
	}

	return true;
}

static void get_route_list(ats::StringList& p_route)
{
	int resp[2];
	pipe(resp);
	const int pid = fork();

	if(!pid)
	{
		close(resp[0]);
		dup2(resp[1], STDOUT_FILENO);
		const char* ip = "/sbin/ip";
		execl(ip, ip, "route", (char*)NULL);
		exit(1);
	}

	close(resp[1]);
	p_route.clear();
	{
		ats::String route_info;

		for(;;)
		{
			char buf[1024];
			const ssize_t nread = read(resp[0], buf, sizeof(buf));

			if(nread <= 0)
			{
				break;
			}

			route_info.append(buf, nread);
		}

		close(resp[0]);
		waitpid(pid, 0, 0);
		ats::split(p_route, route_info, "\n");
	}

}

static void compile_route_list(ats::StringListMap& p_clist, const ats::StringList& p_route)
{
	int i;

	for(i = 0; i < int(p_route.size()); ++i)
	{

		if(white_space_only(p_route[i].c_str()))
		{
			continue;
		}

		CommandBuffer cb;
		init_CommandBuffer(&cb);
		const char* err = gen_arg_list(p_route[i].c_str(), p_route[i].length(), &cb);

		if(!err)
		{
			ats::StringList& sl = p_clist.get(ats::toStr(i));

			if(cb.m_argc > 0)
			{
				sl.push_back("dest");
				sl.push_back(cb.m_argv[0]);
			}

			{
				int i;

				for(i = 1; i < cb.m_argc; i += 2)
				{
					const char* key = cb.m_argv[i];
					const char* val = (i < (cb.m_argc - 1)) ? cb.m_argv[i + 1] : "";
					sl.push_back(key);
					sl.push_back(val);
				}

			}

		}

	}

}

static pthread_t g_check_internet_thread;
static pthread_t g_wifi_led_thread;
static ats::TimerEvent g_check_internet_timer;

static void request_internet_check()
{
	const bool cancel = true;
	g_check_internet_timer.stop_timer(cancel);
}

class InternetIFace;

typedef void* (*IFaceRepairFn)(void*);

class InternetIFace
{
public:
	InternetIFace(int p_metric, IFaceRepairFn p_repair=0)
	{
		m_metric = p_metric;
		m_repair = p_repair;
		m_repair_state = '0';
	}

	void repair();
	void cleanup();

	IFaceRepairFn m_repair;
	int m_repair_state;
	int m_metric;

private:
	pthread_t m_repair_thread;
};

class InternetCheckData
{
public:
	typedef std::map <const ats::String, InternetIFace> InternetIFaceMap;
	typedef std::pair <const ats::String, InternetIFace> InternetIFacePair;
	InternetIFaceMap m_iface;

	ats::String m_ping_server;
	int m_fail_metric;
	int m_ping_timeout;
	int m_monitor_interval;
};

class InternetCheckContext
{
public:
	ats::String m_dev;
	pthread_t m_thread;
	InternetCheckData* m_icd;
	ats::StringList* m_ifnfo;
};

void InternetIFace::repair()
{

	if(!m_repair)
	{
		return;
	}

	if('0' == m_repair_state)
	{
		m_repair_state = 'R'; // Running/Reparing
		const int err = pthread_create(&m_repair_thread, 0, m_repair, this);

		if(err)
		{
			ats_logf(ATSLOG_INFO, "%s,%d: pthread_create failed: (%d) %s", __FUNCTION__, __LINE__, err, strerror(err));
			exit(1);
		}

	}

}

void InternetIFace::cleanup()
{

	if('F' == m_repair_state) // Finished
	{
		pthread_join(m_repair_thread, 0);
		m_repair_state = '0';
	}

}

static int ping(int p_timeout, const ats::String& p_dev, const ats::String& p_host)
{
	const int pid = fork();

	if(!pid)
	{
		const char* ping = "/bin/ping";
		execl(ping, ping, "-c", "1", "-w", ats::toStr(p_timeout).c_str(), "-I", p_dev.c_str(), p_host.c_str(), (char*)NULL);
		exit(1);
	}

	int status;
	waitpid(pid, &status, 0);
	return status;
}

static void* check_internet_connectivity(void* p)
{
	InternetCheckContext& icc = *((InternetCheckContext*)p);
	InternetCheckData& icd = *(icc.m_icd);
	ats::StringList& sl = *(icc.m_ifnfo);
	const ats::String& dev = icc.m_dev;
	const int status = ping(icd.m_ping_timeout, dev, icd.m_ping_server);

	InternetCheckData::InternetIFaceMap::iterator i = icd.m_iface.find(dev);
	InternetIFace* iface = (icd.m_iface.end() == i) ? 0 : &(i->second);
	int metric = (iface) ? iface->m_metric : 0;
	const int cur_metric = get_metric(sl);

	if(WIFEXITED(status) && (0 == WEXITSTATUS(status)))
	{

		if(metric != cur_metric)
		{
			ats_logf(ATSLOG_INFO, "Internet OK on %s, setting metric to %d (from %d)", dev.c_str(), metric, cur_metric);
		}

	}
	else
	{
		metric += icd.m_fail_metric;

		if(metric != cur_metric)
		{
			ats_logf(ATSLOG_INFO, "No Internet on %s, setting metric to %d (from %d)", dev.c_str(), metric, cur_metric);
		}

		if(iface)
		{
			iface->repair();
		}

	}

	while(!delete_route(sl));

	set_metric(sl, metric);
	set_route(sl);

	if(iface)
	{
		iface->cleanup();
	}


	const int statusEthernet = ping(5, "eth0", "8.8.8.8");//ISCP-295
	if (statusEthernet != 0)
    {
        g_RedStoneData.isEthernetInternetWorking(false);
    }
    else
    {
         g_RedStoneData.isEthernetInternetWorking(true);
    }

	return 0;
}

static void stop_pppd()
{
	const int pid = fork();

	if(!pid)
	{
		const char* killall = "/usr/bin/killall";
		execl(killall, killall, "-9", "pppd", (char*)NULL);
		exit(1);
	}

	waitpid(pid, 0, 0);
}

static void* cell_repair(void* p)
{
	InternetIFace& iface = *((InternetIFace*)p);
	stop_pppd();

	// AWARE360-FIXME: For now, PPPD can only be killed once every 3 minutes. This is to prevent
	//	from breaking all cellular communication from killing PPPD too often.
	//
	//	Add handshaking or something so that PPPD is only killed as often as needed (rather than
	//	using an arbitrary delay).
	sleep(180);

	iface.m_repair_state = 'F';
	return 0;
}

static void* wifi_led_thread(void*)
{

	for(;;)
	{
		FILE* f = fopen("/dev/wifitraffic", "r");

		for(;f;)
		{

			if(!g_wifi_running && !g_wifi_client_ip_attached)
			{
				g_RedStoneData.wifiLEDStatus(0);
				sleep(1);
				continue;
			}
			else if(g_wifi_running)
			{

				if(g_wifi_client_ip_attached)
				{
					g_RedStoneData.wifiLEDStatus(2);
				}
				else
				{
					g_RedStoneData.wifiLEDStatus(1);
				}

			}
			else
			{
				g_RedStoneData.wifiLEDStatus(2);
			}

			char c;
			const size_t nread = fread(&c, 1, 1, f);

			if(!nread)
			{
				fclose(f);
				break;
			}

			if('i' == c)
			{
				g_RedStoneData.wifiLEDStatus(3);
			}

			sleep(1);
		}

		sleep(1);
	}

	return 0;
}

static void* monitor_internet_connection(void*)
{
	InternetCheckData icd;
	{
		db_monitor::ConfigDB db;
		db.open_db_config();
		icd.m_fail_metric = db.GetInt("system", "InternetFailMetric", 100);
		icd.m_ping_timeout = db.GetInt("system", "InternetMonitorPingTimeout", 5);
		icd.m_ping_server = db.GetValue("system", "InternetMonitorPingServer", g_google_primary_dns_ip);
		icd.m_monitor_interval = db.GetInt("system", "InternetMonitorInterval", 180);

		icd.m_iface.insert(InternetCheckData::InternetIFacePair("eth0", InternetIFace(db.GetInt("system", "eth0RouteMetric", 0))));
		icd.m_iface.insert(InternetCheckData::InternetIFacePair("ra0", InternetIFace(db.GetInt("system", "ra0RouteMetric", 5))));
		icd.m_iface.insert(InternetCheckData::InternetIFacePair("apcli0", InternetIFace(db.GetInt("system", "apcli0RouteMetric", 4))));
		icd.m_iface.insert(InternetCheckData::InternetIFacePair("ppp0", InternetIFace(db.GetInt("system", "ppp0RouteMetric", 10), cell_repair)));
	}

	for(;;)
	{
		ats::TimerEvent& t = g_check_internet_timer;
		t.start_timer_and_wait(icd.m_monitor_interval, 0);

		ats::StringList routes;
		get_route_list(routes);

		ats::StringListMap slm;
		compile_route_list(slm, routes);

		ats::StringListMap::iterator i = slm.begin();

		std::list <InternetCheckContext> icc_list;

		while(i != slm.end())
		{
			ats::StringList& sl = i->second;
			++i;

			const ats::String& dev = get_default_route(sl);

			if(dev.empty())
			{
				continue;
			}

			icc_list.push_back(InternetCheckContext());
			InternetCheckContext& icc = *(--(icc_list.end()));
			icc.m_dev = dev;
			icc.m_ifnfo = &sl;
			icc.m_icd = &icd;
			check_for_pthread_create_fail(pthread_create(&(icc.m_thread), 0, check_internet_connectivity, &icc), __FUNCTION__, __LINE__);
		}

		{
			std::list <InternetCheckContext>::iterator i = icc_list.begin();

			while(i != icc_list.end())
			{
				InternetCheckContext& icc = *i;
				++i;
				pthread_join(icc.m_thread, 0);
			}

		}

	}

	return 0;
}

static ServerData g_ClientServer;

static void* client_command_server(void* p)
{
	ClientData* cd = (ClientData*)p;

	bool command_too_long = false;

	const size_t max_command_length = 1024;
	char cmdline_buf[max_command_length + 1];
	char* cmdline = cmdline_buf;

	ClientDataCache cdc;
	init_ClientDataCache(&cdc);

	CommandBuffer cb;
	init_CommandBuffer(&cb);

	for(;;)
	{
		char ebuf[256];
		const int c = client_getc_cached(cd, ebuf, sizeof(ebuf), &cdc);

		if(c < 0)
		{
			if(c != -ENODATA) ats_logf(ATSLOG(3), "%s,%d: %s", __FILE__, __LINE__, ebuf);
			break;
		}

		if((c != '\r') && (c != '\n'))
		{
			if(size_t(cmdline - cmdline_buf) >= max_command_length) command_too_long = true;
			else *(cmdline++) = c;

			continue;
		}

		if(command_too_long)
		{
			ats_logf(ATSLOG_INFO, "%s,%d: command is too long", __FILE__, __LINE__);
			cmdline = cmdline_buf;
			command_too_long = false;
			ClientData_send_cmd(cd, MSG_NOSIGNAL, "%s,%d: command is too long\r", __FILE__, __LINE__);
			continue;
		}

		{
			const char* err = gen_arg_list(cmdline_buf, cmdline - cmdline_buf, &cb);
			cmdline = cmdline_buf;

			if(err)
			{
				ats_logf(ATSLOG_INFO, "%s,%d: gen_arg_list failed (%s)", __FILE__, __LINE__, err);
				ClientData_send_cmd(cd, MSG_NOSIGNAL, "%s,%d: gen_arg_list failed: %s\r", __FILE__, __LINE__, err);
				continue;
			}

		}

		if(cb.m_argc <= 0)
		{
			continue;
		}

		const ats::String cmd(cb.m_argv[0]);

		if("check" == cmd)
		{

			if(cb.m_argc >= 2)
			{

				if(0 == strcmp("inet", cb.m_argv[1]))
				{
					request_internet_check();
					ClientData_send_cmd(cd, MSG_NOSIGNAL, "%s: ok\n\r", cmd.c_str());
				}
				else
				{
					ClientData_send_cmd(cd, MSG_NOSIGNAL, "%s: error\nCannot check \"%s\"\n\r", cmd.c_str(), cb.m_argv[1]);
					ClientData_send_cmd(cd, MSG_NOSIGNAL, "%s: \nUsage: check inet\n\r", cmd.c_str());
				}

			}
			else
			{
				ClientData_send_cmd(cd, MSG_NOSIGNAL, "%s: error\nusage: %s <what to check>\n\r", cmd.c_str(), cmd.c_str());
			}

		}
		else
		{
			ClientData_send_cmd(cd, MSG_NOSIGNAL, "error: Invalid command \"%s\"\n\r", cmd.c_str());
		}

	}

	return 0;
}

// Description: Returns true if the interface name "iface" is safe/valid, and false is
//	returned otherwise.
//
//	The application will use the interface name in places where special characters could
//	cause problems. Therefore interface names are first checked for safety/validity.
//
// Return: True is returned if the interface name is safe/valid, and false is returned otherwise.
static bool safe_iface_name(const ats::String& p_iface)
{

	for(size_t i = 0; i < p_iface.length(); ++i)
	{

		const char c = p_iface[i];

		if(isalnum(c))
		{
			continue;
		}

		switch(c)
		{
		case '_':
		case '-': break;
		default: return false;
		}

	}

	return (!(p_iface.empty()));
}

static void add_controlled_interface(
	const ats::String& p_iface,
	const ats::String& p_display_name,
	const ats::String& p_dhcp_envname,
	const ats::String& p_dhcpd_conf_entry_tag,
	const ats::String& p_dhcpkey,
	const ats::String& p_dhcp,
	int p_metric)
{

	if(!safe_iface_name(p_iface))
	{
		ats_logf(ATSLOG_INFO, "Invalid iface name \"%s\", not adding as a controlled interface", p_iface.c_str());
		return;
	}

	ats::StringMap& m = g_iface.get(p_iface);
	g_iface_name.set(p_iface, ats::String());
	m.set("display_name", p_display_name);
	m.set("dhcp_envname", p_dhcp_envname);
	m.set("dhcpd_conf_entry_tag", p_dhcpd_conf_entry_tag);
	m.set("dhcpkey", p_dhcpkey);
	m.set("dhcp", p_dhcp);
	m.set("metric", ats::toStr(p_metric));

	// Generate default DHCP entry for the interface.
	{
		ats::String dhcpd_conf;

		if((ats::getenv(dhcpd_conf, p_dhcp_envname.c_str())).empty())
		{
			ats_logf(ATSLOG_INFO, "%s is empty, will not generate default %s entry", p_dhcp_envname.c_str(), p_display_name.c_str());
		}

		m.set("dhcpd_conf", dhcpd_conf);
		m.set("is_default_dhcpd_conf", "1");
	}

}

int main(int argc, char* argv[])
{
	MyData md;
	int ATSLOG_level;
	md.set("user", "applet");
	md.set("app_name", "network-monitor");

	{
		db_monitor::ConfigDB db;
		//                       iface     Display name   Environment name   DHCPD conf entry tag   ConfigDB DHCP key   DHCP mode   Interface metric
		add_controlled_interface("eth0",   "Ethernet",    "EthernetEntry",   "# ETH0 Ethernet",     "eth0dhcp",         "auto",      db.GetInt("system", "eth0RouteMetric", 0));
		add_controlled_interface("ra0",    "WiFi_AP",     "WiFiEntry",       "# WLAN0 DHCP",        "ra0dhcp",          "on",       db.GetInt("system", "ra0RouteMetric", 5));
		add_controlled_interface("apcli0", "WiFi_CLI",    "WiFi_CLI_Entry",  "# APCLI0 DHCP",       "apcli0dhcp",       "off",      db.GetInt("system", "apcli0RouteMetric", 4));

		g_RouteOverride = db.GetValue("system","RouteOverride");
		g_RouteIP = db.GetValue("system","RouteIP");	// last number in the ethernet address - points to another trulink on the network.
		ATSLOG_level = db.GetInt("RedStone","LogLevel", 0);
	}

	md.set_from_args(argc - 1, argv  + 1);
	const ats::String& app_name = md.get("app_name");
	g_log.set_global_logger(&g_log);
	g_log.set_level(ATSLOG_level);

	g_log.open_testdata(app_name);
	ats_logf(ATSLOG_INFO, "%s started", app_name.c_str());

	pipe(g_out_pipe);
	pipe(g_err_pipe);
	check_for_pthread_create_fail(pthread_create(&g_thread, 0, h_stdout_thread, 0), __FUNCTION__, __LINE__);
	dup2(g_out_pipe[1], STDOUT_FILENO);
	dup2(g_err_pipe[1], STDERR_FILENO);

	stop_all_dhcpd_servers();

	check_for_pthread_create_fail(pthread_create(&(md.m_netlink_thread), 0, netlink_monitor, &md), __FUNCTION__, __LINE__);
	md.wait_for_netlink_monitor_to_be_ready();

	check_for_pthread_create_fail(pthread_create(&g_check_internet_thread, 0, monitor_internet_connection, 0), __FUNCTION__, __LINE__);
	check_for_pthread_create_fail(pthread_create(&g_wifi_led_thread, 0, wifi_led_thread, 0), __FUNCTION__, __LINE__);

	{
		ServerData& sd = g_ClientServer;
		init_ServerData(&sd, 64);
		sd.m_hook = &md;
		sd.m_cs = client_command_server;
		const ats::String& user = md.get("user");
		set_unix_domain_socket_user_group(&sd, user.c_str(), user.c_str());
		::start_redstone_ud_server(&sd, app_name.c_str(), 1);
	}

	// XXX: No network events (no successful "post_network_event") shall be called/performed
	//	within this "for" block (otherwise an infinite loop will result).
	for(;;)
	{
		{
			db_monitor::ConfigDB db;
			md.m_default_resolv_conf = db.GetValue("system", "resolv.conf", "nameserver " + g_google_primary_dns_ip + "\nnameserver 8.8.4.4\n");

			ats::StringMapMap::iterator i = g_iface.begin();

			while(i != g_iface.end())
			{
				ats::StringMap& m = i->second;
				++i;
				m.set("dhcp", db.GetValue("system", m.get("dhcpkey"), m.get("dhcp")));
			}

		}

		md.generate_initial_dhcp_conf_file();

		// Remove any user interface entries (they will be re-added later if needed).
		{
			ats::StringMapMap::iterator i = g_iface.begin();

			while(i != g_iface.end())
			{
				ats::StringMap& m = i->second;
				++i;
				ats::String dhcpd_conf(m.get("dhcpd_conf"));
				bool is_default_dhcpd_conf(m.get_bool("is_default_dhcpd_conf"));
				remove_iface_from_dhcpd_conf(
					m.get("display_name"),
					m.get("dhcpd_conf_entry_tag"),
					dhcpd_conf,
					is_default_dhcpd_conf);
				m.set("dhcpd_conf", dhcpd_conf);
				m.set("is_default_dhcpd_conf", is_default_dhcpd_conf ? "1" : "0");
			}

		}

		// Forget the current DNS interface (if any). The current DNS interface will be reaquired from
		// an interface during the "configure_interface_for_dhcp" step (which follows). If no DNS interface
		// is aquired, then the system default DNS settings will be used.
		md.m_dns_interface.clear();

		// run_dhcp_server: By default is false, meaning the system DHCP server should not run. This will be
		//	set to true if one or more interfaces hosts DHCP, in which case the system DHCP server will
		//	run to service precisely those interfaces.
		bool run_dhcp_server = false;
		{
			ats::StringMapMap::iterator i = g_iface.begin();

			while(i != g_iface.end())
			{
				const ats::String& iface = i->first;
				const ats::StringMap& m = i->second;
				++i;
				run_dhcp_server = configure_interface_for_dhcp(md, iface, m.get("dhcp"), m.get("display_name"), m.get("dhcpd_conf"), m.get_bool("is_default_dhcpd_conf")) ? true : run_dhcp_server;
			}

		}
		run_dhcp_server = false
		apply_dns_resolv_conf(md);

		if(md.m_dns_interface.empty())
		{
			use_default_resolv_conf(md);
		}
		else
		{
			make_sure_resolv_conf_has_primary_google_dns_server(md);
		}

		EventList elist;

		// modify gateway if unit is set up as a secondary source on the network
		if(g_RouteOverride == "secondary")
		{
			ats_logf(ATSLOG_INFO, "Checking secondary routing - routeOverride= %s", g_RouteOverride.c_str());
			set_secondary_route(md);
		}

		if(run_dhcp_server)
		{
			start_dhcpd(md);
			md.wait_for_network_event(elist);
			stop_dhcpd(md);
			// XXX: Include the DHCP state change network event that may have occurred during the "stop_dhcpd" call.
			//	This will allow NetworkMonitor to collapse all network changes into a single loop cycle for
			//	efficiency. If a network event did not occur then it is treated as a "don't-care" (no further
			//	blocking).
			const bool block = false;
			md.wait_for_network_event(elist, block);
		}
		else
		{
			ats_logf(ATSLOG_INFO, "No interfaces ready for DHCP, not running server");
			md.wait_for_network_event(elist);
		}

		{
			size_t i;

			for(i = 0; i < elist.size(); ++i)
			{
				NetworkEvent& ne = *(elist[i]);

				// AWARE360 FIXME: Replace if/else ladder (switch) with a mapping.
				switch(ne.m_type)
				{
				case NetworkEvent::NEWLINK_EVENT:

					if(g_iface.has_key(ne.m_data.m_newlink.m_iface))
					{
						ats::StringMap& m = g_iface.get(ne.m_data.m_newlink.m_iface);
						ats_logf(ATSLOG_INFO, "%d: NEWLINK_EVENT: iface='%s'", __LINE__, ne.m_data.m_newlink.m_iface);
						m.unset("state");
					}

					break;

				case NetworkEvent::DELLINK_EVENT:

					if(g_iface.has_key(ne.m_data.m_dellink.m_iface))
					{
						ats::StringMap& m = g_iface.get(ne.m_data.m_dellink.m_iface);
						ats_logf(ATSLOG_INFO, "%d: DELLINK_EVENT: iface='%s'", __LINE__, ne.m_data.m_newlink.m_iface);
						m.unset("state");
					}

					break;

				case NetworkEvent::NEWADDR_EVENT:

					if(g_iface.has_key(ne.m_data.m_newaddr.m_iface))
					{
						ats::StringMap& m = g_iface.get(ne.m_data.m_newaddr.m_iface);
						ats_logf(ATSLOG_INFO, "%d: NEWADDR_EVENT: iface='%s'", __LINE__, ne.m_data.m_newlink.m_iface);
						m.unset("state");
					}

					break;

				case NetworkEvent::DHCP_EVENT:

					if(g_iface.has_key(ne.m_data.m_dhcp.m_iface))
					{
						ats::StringMap& m = g_iface.get(ne.m_data.m_dhcp.m_iface);
						ats_logf(ATSLOG_INFO, "%d: DHCP_EVENT: iface='%s'", __LINE__, ne.m_data.m_newlink.m_iface);
						m.unset("state");
					}

					break;
				}

			}

		}

		delete_event_list(elist);
	}

	return 0; // Never reached
}
