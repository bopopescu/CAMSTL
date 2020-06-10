<?php require_once $_SERVER['DOCUMENT_ROOT'].'inc/session_controller.inc'; ?>
<!DOCTYPE HTML>

<html>
	<head>
		<meta charset="UTF-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<title>VPN - <?php echo DEVICE_NAME; ?></title>

		<?php include $_SERVER['DOCUMENT_ROOT'].'mainscriptsgroup.php'; ?>
		<script type="text/javascript" src="/js/ipsec.js"></script>
		<script type="text/javascript" src="/js/vpn.js"></script>
	
	</head>

<body>
	<div class="container">
		<?php include $_SERVER['DOCUMENT_ROOT'].'header.php'; ?>

		<div class="clear"></div>
		<div class="clear"></div>

		<?php include $_SERVER['DOCUMENT_ROOT'].'tabs.php'; ?>

		<div class="contentblock">

			<!-- Network Config tab -->
			<h2>Network Configuration</h2>
			
			<?php include '../networktabs.php'; ?>		

				<div class="contentblock2">
					<!---------------------------------- VPN SUBTAB ------------------------------------------>
					<div class="msgBox"></div>
					<?php require_once $_SERVER['DOCUMENT_ROOT'].'inc/ipsec_view.inc'; ?>
				
					<!-- Header -->
					<div class="inversetab">VPN</div>
					<!--- <a href="/TL3000_HTML5/Default_CSH.htm#VPN" TARGET="_blank"><img src="/images/help.png" alt="help" border="0" ></a> -->
					<div class="hr"><hr /></div>
					
					<div id="vpn" class="level3tabs">
						<!-- VPN subsubtabs -->
						<ul>
							<li><a href="#vpn-activelist">Summary</a></li>
							<li><a href="#vpn-ipsec">IP Sec</a></li>
<!--
							<?php if(!empty($_SESSION['M2M_SESH_USERAL']) && $_SESSION['M2M_SESH_USERAL'] == 1){ ?><li><a href="#vpn-pp2p" class="inactive">PP2P</a></li> <?php } ?>
							<?php if(!empty($_SESSION['M2M_SESH_USERAL']) && $_SESSION['M2M_SESH_USERAL'] == 1){ ?><li><a href="#vpn-lt2p" class="inactive">LT2P</a></li> <?php } ?>
							<?php if(!empty($_SESSION['M2M_SESH_USERAL']) && $_SESSION['M2M_SESH_USERAL'] == 1){ ?><li><a href="#vpn-openvpn" class="inactive">OpenVPN</a></li> <?php } ?>
-->
						</ul>
						<div id="vpn-activelist">
							<!---------------------------------- Summary SubSubTab ------------------------------------------>
							<div style="width: 485px; height: 400px; margin:0 0 0 -8%;">
								<?php echo (!empty($active_ipsec) ? $active_ipsec : ''); ?>
							</div>
						</div>

						<div id="vpn-ipsec">
							<!---------------------------------- IPSec SubSubTab ------------------------------------------>
							<form class="formCheck" id="ipsec" method="post" action="/inc/ipsec_processor.php">
								<input type="hidden" name="vpn" value="ipsec" />
								<br />
								<div class="inversetab">General</div>
								<div class="hr"> <hr /> </div>
								<div class="row">
									<span class="label2">Policy Name</span>
									<span class="formw2 reg scCheck">
										<input type="text" size="26" name="ipsecPolicyName" value="<?php echo (isset($ipsec['policy']) ? $ipsec['policy'] : ''); ?>"> 
										<span class="errorMsg" name="errorPN"></span>
										<br/> 
										<span class="fieldMessage">*Policy name can only be a-z, 0-9 , '_', or '-'</span>
									</span>
								</div>
								<div class="row">
									<span class="label2">IP Sec Mode</span>
									<span class="formw2">
										<select name="ipsecmode" style="width: 156px;">
											<option <?php echo ((strcasecmp($ipsec['mode'],'Tunnel') == 0) ? 'selected="selected"':'');?> value="Tunnel" selected="selected">Tunnel</option>
											<option <?php echo ((strcasecmp($ipsec['mode'],'Transport') == 0) ? 'selected="selected"':'');?> value="Transport">Transport</option>
										</select>
									</span>
								</div>
								<div class="row">
									<span class="label2">Remote End Point</span>
									<span class="formw2">
										<select name="ipsecEPtype" style="width: 156px;">
											<option <?php echo ((strcasecmp($ipsec['REtype'],'FQDN') == 0) ? 'selected="selected"':'');?> value="FQDN">FQDN</option>
											<option <?php echo ((strcasecmp($ipsec['REtype'],'IP') == 0) ? 'selected="selected"':'');?> value="IP">IP Address</option>
										</select>
									</span>
								</div>
								<div class="row" name="ipsecRemIPD">
									<span class="label2">Remote IP</span>
									<span class="formw2 ip" id="ipsecREMIP">
										<input type="text" size="2" maxlength="3" name="ipsecREMSIP1" class="ip1" value="<?php echo (isset($ipsec['RE_ip'][0]) ? $ipsec['RE_ip'][0] : ''); ?>">
										<span name="remfirstdot">.</span>
										<input type="text" size="2" maxlength="3" name="ipsecREMSIP2" class="ip2" value="<?php echo (isset($ipsec['RE_ip'][1]) ? $ipsec['RE_ip'][1] : ''); ?>">
										<span name="remseconddot">.</span>
										<input type="text" size="2" maxlength="3" name="ipsecREMSIP3" class="ip3" value="<?php echo (isset($ipsec['RE_ip'][2]) ? $ipsec['RE_ip'][2] : ''); ?>">
										<span name="remthirddot">.</span>
										<input type="text" size="2" maxlength="3" name="ipsecREMSIP4" class="ip4" value="<?php echo (isset($ipsec['RE_ip'][3]) ? $ipsec['RE_ip'][3] : ''); ?>">
										<span class="errorMsg" name="errorRemIP"></span>
									</span>
								</div>
								<div class="row" name="ipsecRemFQDND">
									<span class="label2">Remote FQDN</span>
									<span class="formw2 reg">
										<input type="text" size="26" name="ipsecREMfqdn" value="<?php echo (isset($ipsec['RE_fqdn']) ? $ipsec['RE_fqdn'] : ''); ?>">
										<span class="errorMsg" name="errorRemFQDN"></span>
									</span>
								</div>
                                <div class="row">
									<span class="label2">Mode</span>
									<span class="formw2">

										<input type=radio name="vpnMode" value="Client" <?php echo ((strcasecmp($ipsec['vpnmode'],'Client') == 0) ? 'checked="checked"':'');?> />Client to Gateway&nbsp;&nbsp;
										<input type=radio name="vpnMode" value="GateWay" <?php echo ((strcasecmp($ipsec['vpnmode'],'GateWay') == 0) ? 'checked="checked"':'');?> />Gateway to Gateway 
									</span>
								</div>

								<div class="row">
									<span class="label2">Local Network Type</span>
									<span class="formw2">
										<select name="ipsecLOCtype" style="width: 156px;">
											<option <?php echo ((strcasecmp($ipsec['LN_type'],'Range') == 0) ? 'selected="selected"':'');?> value="Range">Range</option>
											<option <?php echo ((strcasecmp($ipsec['LN_type'],'Subnet') == 0) ? 'selected="selected"':'');?> value="Subnet">Subnet</option>
										</select>
									</span>
								</div>
								<div class="row">
									<span class="label2">Local Start IP Address</span>
									<span class="formw2 ip" id="ipsecLSIP">
										<input type="text" size="2" maxlength="3" name="ipsecLSIP1" class="ip1" value="<?php echo (isset($ipsec['LN_sip'][0]) ? $ipsec['LN_sip'][0] : ''); ?>">.
										<input type="text" size="2" maxlength="3" name="ipsecLSIP2" class="ip2" value="<?php echo (isset($ipsec['LN_sip'][1]) ? $ipsec['LN_sip'][1] : ''); ?>">.
										<input type="text" size="2" maxlength="3" name="ipsecLSIP3" class="ip3" value="<?php echo (isset($ipsec['LN_sip'][2]) ? $ipsec['LN_sip'][2] : ''); ?>">.
										<input type="text" size="2" maxlength="3" name="ipsecLSIP4" class="ip4" value="<?php echo (isset($ipsec['LN_sip'][3]) ? $ipsec['LN_sip'][3] : ''); ?>">
										<span class="errorMsg" name="errorLSIP"></span>
									</span>
								</div>
								<div class="row">
									<span class="label2">Local End IP Address</span>
									<span class="formw2 ip" id="ipsecLEIP">
										<input type="text" size="2" maxlength="3" name="ipsecLEIP1" class="ip1" value="<?php echo (isset($ipsec['LN_eip'][0]) ? $ipsec['LN_eip'][0] : ''); ?>">.
										<input type="text" size="2" maxlength="3" name="ipsecLEIP2" class="ip2" value="<?php echo (isset($ipsec['LN_eip'][1]) ? $ipsec['LN_eip'][1] : ''); ?>">.
										<input type="text" size="2" maxlength="3" name="ipsecLEIP3" class="ip3" value="<?php echo (isset($ipsec['LN_eip'][2]) ? $ipsec['LN_eip'][2] : ''); ?>">.
										<input type="text" size="2" maxlength="3" name="ipsecLEIP4" class="ip4" value="<?php echo (isset($ipsec['LN_eip'][3]) ? $ipsec['LN_eip'][3] : ''); ?>"> 
										<span class="errorMsg" name="errorLEIP"></span>
									</span>
								</div>
								<div class="row">
									<span class="label2">Local IP Subnet Mask</span>
									<span class="formw2 ip" id="ipsecLIPSN">
										<input type="text" size="2" maxlength="3" name="ipsecLIPSN1" class="ip1" value="<?php echo (isset($ipsec['LN_subnet'][0]) ? $ipsec['LN_subnet'][0] : ''); ?>">.
										<input type="text" size="2" maxlength="3" name="ipsecLIPSN2" class="ip2" value="<?php echo (isset($ipsec['LN_subnet'][1]) ? $ipsec['LN_subnet'][1] : ''); ?>">.
										<input type="text" size="2" maxlength="3" name="ipsecLIPSN3" class="ip3" value="<?php echo (isset($ipsec['LN_subnet'][2]) ? $ipsec['LN_subnet'][2] : ''); ?>">.
										<input type="text" size="2" maxlength="3" name="ipsecLIPSN4" class="ip4" value="<?php echo (isset($ipsec['LN_subnet'][3]) ? $ipsec['LN_subnet'][3] : ''); ?>">
										<span class="errorMsg" name="errorLIPSN"></span>
									</span>
								</div>
								<div class="row">
									<span class="label2">Remote Network Type</span>
									<span class="formw2">
										<select name="ipsecREMtype" style="width: 156px;">
											<option <?php echo ((strcasecmp($ipsec['RN_type'],'Range') == 0) ? 'selected="selected"':'');?> value="Range">Range</option>
											<option <?php echo ((strcasecmp($ipsec['RN_type'],'Subnet') == 0) ? 'selected="selected"':'');?> value="Subnet">Subnet</option>
										</select>
									</span>
								</div>
								<div class="row">
									<span class="label2">Remote Start IP Address</span>
									<span class="formw2 ip" id="ipsecRSIP">
										<input type="text" size="2" maxlength="3" name="ipsecRSIP1" class="ip1" value="<?php echo (isset($ipsec['RN_sip'][0]) ? $ipsec['RN_sip'][0] : ''); ?>">.
										<input type="text" size="2" maxlength="3" name="ipsecRSIP2" class="ip2" value="<?php echo (isset($ipsec['RN_sip'][1]) ? $ipsec['RN_sip'][1] : ''); ?>">.
										<input type="text" size="2" maxlength="3" name="ipsecRSIP3" class="ip3" value="<?php echo (isset($ipsec['RN_sip'][2]) ? $ipsec['RN_sip'][2] : ''); ?>">.
										<input type="text" size="2" maxlength="3" name="ipsecRSIP4" class="ip4" value="<?php echo (isset($ipsec['RN_sip'][3]) ? $ipsec['RN_sip'][3] : ''); ?>">
										<span class="errorMsg" name="errorRSIP"></span>
									</span>
								</div>
								<div class="row">
									<span class="label2">Remote End IP Address</span>
									<span class="formw2 ip" id="ipsecREIP">
										<input type="text" size="2" maxlength="3" name="ipsecREIP1" class="ip1" value="<?php echo (isset($ipsec['RN_eip'][0]) ? $ipsec['RN_eip'][0] : ''); ?>">.
										<input type="text" size="2" maxlength="3" name="ipsecREIP2" class="ip2" value="<?php echo (isset($ipsec['RN_eip'][1]) ? $ipsec['RN_eip'][1] : ''); ?>">.
										<input type="text" size="2" maxlength="3" name="ipsecREIP3" class="ip3" value="<?php echo (isset($ipsec['RN_eip'][2]) ? $ipsec['RN_eip'][2] : ''); ?>">.
										<input type="text" size="2" maxlength="3" name="ipsecREIP4" class="ip4" value="<?php echo (isset($ipsec['RN_eip'][3]) ? $ipsec['RN_eip'][3] : ''); ?>"> 
										<span class="errorMsg" name="errorREIP"></span>
									</span>
								</div>
								<div class="row">
									<span class="label2">Remote IP Subnet Mask</span>
									<span class="formw2 ip" id="ipsecRIPSN">
										<input type="text" size="2" maxlength="3" name="ipsecRIPSN1" class="ip1" value="<?php echo (isset($ipsec['RN_subnet'][0]) ? $ipsec['RN_subnet'][0] : ''); ?>">.
										<input type="text" size="2" maxlength="3" name="ipsecRIPSN2" class="ip2" value="<?php echo (isset($ipsec['RN_subnet'][1]) ? $ipsec['RN_subnet'][1] : ''); ?>">.
										<input type="text" size="2" maxlength="3" name="ipsecRIPSN3" class="ip3" value="<?php echo (isset($ipsec['RN_subnet'][2]) ? $ipsec['RN_subnet'][2] : ''); ?>">.
										<input type="text" size="2" maxlength="3" name="ipsecRIPSN4" class="ip4" value="<?php echo (isset($ipsec['RN_subnet'][3]) ? $ipsec['RN_subnet'][3] : ''); ?>">
										<span class="errorMsg" name="errorRIPSN"></span>
									</span>
								</div>
								<div class="row">
									<span class="label2">Keep Alive</span>
									<span class="formw2"> 
										<input type=radio name="ipseckeepAlive" value="On" <?php echo ((strcasecmp($ipsec['keep_alive'],'On') == 0) ? 'checked="checked"':'');?> />On&nbsp;&nbsp;
										<input type=radio name="ipseckeepAlive" value="Off" <?php echo ((strcasecmp($ipsec['keep_alive'],'Off') == 0) ? 'checked="checked"':'');?> /> Off
									</span>
								</div>

								<div clear:both></div>
								<br /> <br /> <br />
								<div class="inversetab">Phase 1 (IKE SA Parameters)</div>
								<div class="hr">
									<hr />
								</div>
								<div class="row">
									<span class="label2">Exchange Mode</span>
									<span class="formw2">
										<select name="ipsecexchangeMode" style="width: 156px;">
											<option <?php echo ((strcasecmp($ipsec['exchange'],'Main') == 0) ? 'selected="selected"':'');?> value="Main">Main</option>
											<option <?php echo ((strcasecmp($ipsec['exchange'],'Aggressive') == 0) ? 'selected="selected"':'');?> value="Aggressive">Aggressive</option>
									</select>
									</span>
								</div>
								<div class="row">
									<span class="label2">NAT Traversal</span>
									<span class="formw2">
										<input type=radio name="ipsecnatTrav" value="On" <?php echo ((strcasecmp($ipsec['nat'],'On') == 0) ? 'checked="checked"':'');?> />On&nbsp;&nbsp;
										<input type=radio name="ipsecnatTrav" value="Off" <?php echo ((strcasecmp($ipsec['nat'],'Off') == 0) ? 'checked="checked"':'');?> /> Off
									</span>
								</div>
								<div class="row">
									<span class="label2">NAT Keep Alive Frequency (in seconds)</span>
									<span class="formw2">
										<input type="text" size="26" name="ipsecNatFreq" value="<?php echo (isset($ipsec['nat_ka']) ? $ipsec['nat_ka'] : ''); ?>">
										
									</span>
								</div>
								<div class="row">
									<span class="label2">Local ID Type</span>
									<span class="formw2">
										<select name="ipsecLIDT" style="width: 156px;">
											<option <?php echo ((strcasecmp($ipsec['LID_type'],'IP') == 0) ? 'selected="selected"':'');?> value="IP">Local WAN IP</option>
											<option <?php echo ((strcasecmp($ipsec['LID_type'],'FQDN') == 0) ? 'selected="selected"':'');?> value="FQDN">FQDN</option>
											<option <?php echo ((strcasecmp($ipsec['LID_type'],'Email') == 0) ? 'selected="selected"':'');?> value="Email">Email</option>
										</select>
									</span>
								</div>
								<div class="row" name="ipseclocalIDFE">
									<span class="label2">Local ID FQDN/Email</span>
									<span class="formw2">
										<input type="text" size="26" name="ipseclocalID" value="<?php echo (isset($ipsec['LID_fqdn']) ? $ipsec['LID_fqdn'] : ''); ?>">
										
									</span>
								</div>
								<div class="row" name="ipseclocalIDIP">
									<span class="label2">Local ID IP</span>
									<span class="formw2" id="LIDIP">
										<input type="text" size="2" maxlength="3" name="ipsecLWANIP1" class="ip1" value="<?php echo (isset($ipsec['LID_ip'][0]) ? $ipsec['LID_ip'][0] : ''); ?>">
										<span name="lfirstdot">.</span>
										<input type="text" size="2" maxlength="3" name="ipsecLWANIP2" class="ip2" value="<?php echo (isset($ipsec['LID_ip'][1]) ? $ipsec['LID_ip'][1] : ''); ?>">
										<span name="lseconddot">.</span>
										<input type="text" size="2" maxlength="3" name="ipsecLWANIP3" class="ip3" value="<?php echo (isset($ipsec['LID_ip'][2]) ? $ipsec['LID_ip'][2] : ''); ?>">
										<span name="lthirddot">.</span>
										<input type="text" size="2" maxlength="3" name="ipsecLWANIP4" class="ip4" value="<?php echo (isset($ipsec['LID_ip'][3]) ? $ipsec['LID_ip'][3] : ''); ?>">
										
									</span>
								</div>								
								<div class="row">
									<span class="label2">Remote ID Type</span>
									<span class="formw2">
										<select name="ipsecRIT" style="width: 156px;">
											<option <?php echo ((strcasecmp($ipsec['RID_type'],'IP') == 0) ? 'selected="selected"':'');?> value="IP">Remote WAN IP</option>
											<option <?php echo ((strcasecmp($ipsec['RID_type'],'FQDN') == 0) ? 'selected="selected"':'');?> value="FQDN">FQDN</option>
										</select>
									</span>
								</div>
								<div class="row" name="ipsecRemIDF">
									<span class="label2">Remote ID FQDN</span>
									<span class="formw2">
										<input type="text" size="26" name="ipsecremoteID" value="<?php echo (isset($ipsec['RID_fqdn']) ? $ipsec['RID_fqdn'] : ''); ?>">
										 
									</span>
								</div>
								<div class="row" name="ipsecRemIDIP">
									<span class="label2">Remote ID IP</span>
									<span class="formw2" id="ipsecRWANIP"> 
										<input type="text" size="2" maxlength="3" name="ipsecRWANIP1" class="ip1" value="<?php echo (isset($ipsec['RID_ip'][0]) ? $ipsec['RID_ip'][0] : ''); ?>"><span name="rfirstdot">.</span>
										<input type="text" size="2" maxlength="3" name="ipsecRWANIP2" class="ip2" value="<?php echo (isset($ipsec['RID_ip'][1]) ? $ipsec['RID_ip'][1] : ''); ?>"><span name="rseconddot">.</span>
										<input type="text" size="2" maxlength="3" name="ipsecRWANIP3" class="ip3" value="<?php echo (isset($ipsec['RID_ip'][2]) ? $ipsec['RID_ip'][2] : ''); ?>"><span name="rthirddot">.</span>
										<input type="text" size="2" maxlength="3" name="ipsecRWANIP4" class="ip4" value="<?php echo (isset($ipsec['RID_ip'][3]) ? $ipsec['RID_ip'][3] : ''); ?>">
										
									</span>
								</div>
								<div class="row">
									<span class="label2">Encryption</span>
									<span class="formw2">
										<select name="ipsecphase1e" style="width: 156px;">
											<option <?php echo ((strcasecmp($ipsec['encryp_p1'],1) == 0) ? 'selected="selected"':'');?> value="1">DES</option>
											<option <?php echo ((strcasecmp($ipsec['encryp_p1'],2) == 0) ? 'selected="selected"':'');?> value="2">3DES</option>
											<option <?php echo ((strcasecmp($ipsec['encryp_p1'],3) == 0) ? 'selected="selected"':'');?> value="3">AES-128</option>
											<option <?php echo ((strcasecmp($ipsec['encryp_p1'],4) == 0) ? 'selected="selected"':'');?> value="4">AES-192</option>
											<option <?php echo ((strcasecmp($ipsec['encryp_p1'],5) == 0) ? 'selected="selected"':'');?> value="5">AES-256</option>
										</select>
									</span>
								</div>
								<div class="row">					
									<span class="label2">Authentication</span>
									<span class="formw2">
										<select name="ipsecphase1a" style="width: 156px;">
											<option <?php echo ((strcasecmp($ipsec['auth_p1'],1) == 0) ? 'selected="selected"':'');?> value="1">MD5</option>
											<option <?php echo ((strcasecmp($ipsec['auth_p1'],2) == 0) ? 'selected="selected"':'');?> value="2">SHA-1</option>
											<option <?php echo ((strcasecmp($ipsec['auth_p1'],3) == 0) ? 'selected="selected"':'');?> value="3">SHA2-256</option>
											<option <?php echo ((strcasecmp($ipsec['auth_p1'],4) == 0) ? 'selected="selected"':'');?> value="4">SHA2-384</option>
											<option <?php echo ((strcasecmp($ipsec['auth_p1'],5) == 0) ? 'selected="selected"':'');?> value="5">SHA2-512</option>
										</select>
									</span>
								</div>
								<div class="row">
									<span class="label2">Authentication Method</span>
									<span class="formw2"> 
										<select name="ipsecphase1am" style="width: 156px;">
											<option value="Preshared" <?php echo ((strcasecmp($ipsec['auth_type'],'Preshared') == 0) ? 'selected="selected"':'');?> >Pre-Shared</option>
											<!-- <option value="RSA" <//?php echo ((strcasecmp($ipsec['encryp_p2'],'RSA') == 0) ? 'selected="selected"':'');?> >RSA-Signature</option>
											<option value="X509" <//?php echo ((strcasecmp($ipsec['encryp_p2'],5) == 'X509') ? 'selected="selected"':'');?> >X509 Certification</option>-->
										</select>
									</span>
								</div>
								<div class="row">
									<span class="label2">Preshared Key</span>
									<span class="formw2">
										<input type="text" size="26" name="ipsecpskey" value="<?php echo (isset($ipsec['ps_key']) ? $ipsec['ps_key'] : ''); ?>"> 
										<span class="errorMsg" name="errorMPK"></span>
									</span>
								</div>
								<div class="row">
									<span class="label2">DH Group</span>
									<span class="formw2">
										<select name="ipsecphase1dh" style="width: 156px;">
											<option <?php echo ((strcasecmp($ipsec['dh_group'],1) == 0) ? 'selected="selected"':'');?>  value="1">Group 1 (768 bit)</option>
											<option <?php echo ((strcasecmp($ipsec['dh_group'],2) == 0) ? 'selected="selected"':'');?>  value="2">Group 2 (1024 bit)</option>
											<option <?php echo ((strcasecmp($ipsec['dh_group'],5) == 0) ? 'selected="selected"':'');?>  value="5">Group 5 (1536 bit)</option>
										</select>
									</span>
								</div>
								<div class="row">
									<span class="label2">SA Lifetime (seconds)</span>
									<span class="formw2">
										<input type="text" size="26" name="ipsecsalife" value="<?php echo (isset($ipsec['sa_life_p1']) ? $ipsec['sa_life_p1'] : ''); ?>"> 
										
									</span>
								</div>
								<div class="row">
									<span class="label2">Dead Peer Detection</span>
									<span class="formw2"> 
										<input type=radio name="ipsecdeadPeer" value="On" <?php echo ((strcasecmp($ipsec['dpd'],'On') == 0) ? 'checked="checked"':'');?> />On&nbsp;&nbsp;
										<input type=radio name="ipsecdeadPeer" value="Off" <?php echo ((strcasecmp($ipsec['dpd'],'Off') == 0) ? 'checked="checked"':'');?> /> Off
									</span>
								</div>
								<br /> <br /> <br />
								<div class="inversetab">Phase 2 (IKE SA Parameters)</div>
								<div class="hr"> <hr /> </div>
								<div class="row">
									<span class="label2">SA Lifetime (seconds)</span>
									<span class="formw2">
										<input type="text" size="26" name="ipsecphase2salife" value="<?php echo (isset($ipsec['sa_life_p2']) ? $ipsec['sa_life_p2'] : ''); ?>">
										
									</span>
								</div>
								<div class="row">
									<span class="label2">Encryption</span>
									<span class="formw2">
										<select name="ipsecphase2e" style="width: 156px;">
											<option <?php echo ((strcasecmp($ipsec['encryp_p2'],1) == 0) ? 'selected="selected"':'');?> value="1">DES</option>
											<option <?php echo ((strcasecmp($ipsec['encryp_p2'],2) == 0) ? 'selected="selected"':'');?> value="2">3DES</option>
											<option <?php echo ((strcasecmp($ipsec['encryp_p2'],3) == 0) ? 'selected="selected"':'');?> value="3">AES-128</option>
											<option <?php echo ((strcasecmp($ipsec['encryp_p2'],4) == 0) ? 'selected="selected"':'');?> value="4">AES-192</option>
											<option <?php echo ((strcasecmp($ipsec['encryp_p2'],5) == 0) ? 'selected="selected"':'');?> value="5">AES-256</option>
										</select>
									</span>
								</div>
								<div class="row">
									<span class="label2">Authentication</span>
									<span class="formw2">
										<select	name="ipsecphase2a" style="width: 156px;">
											<option <?php echo ((strcasecmp($ipsec['auth_p2'],1) == 0) ? 'selected="selected"':'');?> value="1">MD5</option>
											<option <?php echo ((strcasecmp($ipsec['auth_p2'],2) == 0) ? 'selected="selected"':'');?> value="2">SHA-1</option>
											<option <?php echo ((strcasecmp($ipsec['auth_p2'],3) == 0) ? 'selected="selected"':'');?> value="3">SHA2-256</option>
											<option <?php echo ((strcasecmp($ipsec['auth_p2'],4) == 0) ? 'selected="selected"':'');?> value="4">SHA2-384</option>
											<option <?php echo ((strcasecmp($ipsec['auth_p2'],5) == 0) ? 'selected="selected"':'');?> value="5">SHA2-512</option>
										</select>
									</span>
								</div>
								<div class="row">
									<span class="label2">PFS</span>
									<span class="formw2"> 
										<input type=radio name="ipsecpfs" value="On" <?php echo ((strcasecmp($ipsec['pfs'],'On') == 0) ? 'checked="checked"':'');?>  />On&nbsp;&nbsp;
										<input type=radio name="ipsecpfs" value="Off" <?php echo ((strcasecmp($ipsec['pfs'], 'Off') == 0) ? 'checked="checked"':'');?> /> Off
									</span>
								</div>
								<div class="spacer">&nbsp;</div>
							<?php
								if($_SESSION['M2M_SESH_USERAL'] < 300)
								{
							?>	
								<div class="row">
									<span class="formw2">
										<button class="button2-link" type="submit">Save</button>&nbsp;
										<button class="button3-link" type="reset">Cancel</button>&nbsp;
										<button class="button4-link">Sync</button>
									</span>
								</div>
							<?php
								}
							?>
							</form>
						</div>
					</div> <!-- end of VPN (third) subtabs container -->
				</div> <!--end of VPN block container -->
		</div><!-- end of entire div contentblock -->
	</div><!-- end of entire div container -->
</body>
</html>
