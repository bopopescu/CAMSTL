#include <stdio.h>
#include <sys/socket.h>
#include <sys/types.h>
#include <stdlib.h>
#include <string.h>
#include <errno.h>
#include <pthread.h>
#include <fcntl.h>
#include <semaphore.h>

#include "ats-common.h"
#include "socket_interface.h"
#include "command_line_parser.h"

#define IF_DEBUG(M) if(g_md.m_dbg >= (M))

class MyData : public ats::CommonData
{
public:

	ClientSocket m_cs;
	int m_src_fd;
	int m_des_fd;
	int m_dbg;

	MyData()
	{
		m_dbg = 0;
		m_src_fd = -1;
		m_des_fd = -1;
		init_ClientSocket(&m_cs);
	}

};

MyData g_md;

static void* recv_thread(void* p)
{
	MyData& md = *((MyData*)p);

	for(;;)
	{
		IF_DEBUG(5) fprintf(stderr, "Recving from socket\n");

		char buf[8192];
		const ssize_t nread = recv(md.m_cs.m_fd, buf, sizeof(buf), 0);

		if(nread <= 0)
		{
			IF_DEBUG(5) fprintf(stderr, "Failed to recv from socket\n");
			break;
		}

		IF_DEBUG(5) fprintf(stderr, "Writing %d to destination\n", int(nread));
		write(md.m_des_fd, buf, size_t(nread));
	}

	return 0;
}

int main(int argc, char* argv[])
{

	if(argc < 4)
	{
		fprintf(stderr, "usage: %s <src> <des> <unix domain socket>\n", argv[0]);
		return 1;
	}

	const bool uc = !strcmp("uc", argv[0]);

	MyData& md = g_md;
	md.set_from_args(argc - 3, argv + 3);
	md.m_dbg = md.get_int("debug");

	ClientSocket& cs = md.m_cs;
	init_ClientSocket(&cs);

	const char* src = argv[1];
	const char* des = argv[2];
	const char* name = argv[3];


	if(!strcmp("-", src))
	{
		md.m_src_fd = STDIN_FILENO;
	}
	else
	{

		if((md.m_src_fd = open(src, O_RDONLY)) < 0)
		{
			fprintf(stderr, "Could not open \"%s\" for reading: %d: %s\n", src, errno, strerror(errno));
			return 1;
		}

	}

	if(!strcmp("-", des))
	{
		md.m_des_fd = STDOUT_FILENO;
	}
	else
	{

		if((md.m_des_fd = open(des, O_APPEND | O_WRONLY)) < 0)
		{
			fprintf(stderr, "Could not open \"%s\" for append: %d: %s\n", des, errno, strerror(errno));
		}

	}

	const int ret = uc ? connect_unix_domain_client(&cs, name) : connect_redstone_ud_client(&cs, name);

	if(ret < 0)
	{
		fprintf(stderr, "Failed to connect to socket: %d\n", ret);
		return 1;
	}

	pthread_t thread;
	pthread_create(&thread, 0, recv_thread, &md);

	for(;;)
	{
		IF_DEBUG(5) fprintf(stderr, "reading from source\n");
		char buf[8192];
		const ssize_t nread = read(md.m_src_fd, buf, sizeof(buf));

		if(nread <= 0)
		{
			IF_DEBUG(5) fprintf(stderr, "Failed to read from src: %d, %s\n", errno, strerror(errno));
			break;
		}

		IF_DEBUG(5) fprintf(stderr, "writing %d to socket\n", int(nread));
		const ssize_t nwrite = send(cs.m_fd, buf, nread, MSG_NOSIGNAL);

		if(nwrite <= 0)
		{
			IF_DEBUG(5) fprintf(stderr, "Failed to write to socket\n");
			break;
		}

	}

	IF_DEBUG(5) fprintf(stderr, "Waiting to joing thread\n");
	pthread_join(thread, 0);
	IF_DEBUG(5) fprintf(stderr, "Thread joined\n");
	return 0;
}
