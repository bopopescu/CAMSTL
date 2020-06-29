#include "socket_interface.h"
#include "atslogger.h"

#include "ATCCID_AMH.h"

void ATCCID_AMH::on_message(MyData& p_md, const ats::String& p_cmd, const ats::String& p_msg)
{
	const ats::String& response = p_msg.substr(p_cmd.length());

	if(!(response.empty()))
	{
		const ats::String& ccid = response.substr(7); 

		if(ccid.size() == 20 )
		{
			send_trulink_ud_msg("admin-client-cmd", 0, "atcmd cellccid %s\r", ccid.c_str());
			ats_logf(ATSLOG_DEBUG, "atcmd ccid: %s", ccid.c_str());
		}

	}

}
