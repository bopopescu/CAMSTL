<?php
	$list = `wifi-monitor --show`;
	$list = explode("\n", $list);
?>
	<form id="enable_wifi" name="enable_wifi" action="/inc/wifi/update_wifi.php">
		<span class="label2">WiFi Client Mode</span> <span class="formw2">
		<input type="radio" name="wifi-client-enable" value="1" <?php echo !isOff($wifi_client_enable_status) ? 'checked="checked"' : '';?> />On&nbsp;&nbsp;
		<input type="radio" name="wifi-client-enable" value="0" <?php echo isOff($wifi_client_enable_status) ? 'checked="checked"' : '';?> /> Off
		</span>
		<div class="row">
		<span class="formw2">
			<button class="button2-link" type="submit">Save</button>&nbsp;
			<button class="button3-link" type="reset">Cancel</button>&nbsp;
		</span>
		</div>
	</form>
	<div class="row"/>
<?php
	print "<h3>Add a new Wi-Fi Network</h3>";
	print "<table><thead><tr><th>SSID</th><th>Password</th><th></th></tr></thead>";
	print "<tbody><tr>";
	print "<td><input id=\"newSSID\" type=\"text\" placeholder=\"Enter SSID\"/></td>";
	print "<td><input id=\"newPass\" type=\"password\" placeholder=\"Enter Password\"/></td>";
	print "<td><input type=\"button\" value=\"Add\" onclick=\"add_wifi()\"/></td>";
	print "</tr></tbody></table>";
?>
	<form id="update_wifi" name="update_wifi" action="/inc/wifi/update_wifi.php">
	<input type="hidden" id="updateSSID" name="updateSSID"/>
	<input type="hidden" id="updatePass" name="updatePass"/>
	</form>
<?php
	print "<h3>Saved Wi-Fi Networks</h3>";
	print "<table id=\"wifi_networks_table\"><thead><tr><th style=\"width:45%\" >SSID</th><th style=\"width:45%\">Password</th><th>Edit</th><th>Delete</th></tr></thead><tbody>";
	$i = 0;

	foreach($list as $line)
	{

		if(preg_match('/^username=([^ ]+)( password=([^ ]+))?/', $line, $m))
		{
			$s = hex2bin($m[1]);
			$p = isset($m[3]) ? hex2bin($m[3]) : '';
			print "<tr><td id=\"ssid$i\">$s</td><td id=\"pass$i\">***</td>";

			print "<td><input type=\"image\" src=\"/images/EditIcon16.png\" id=\"edit$i\" alt=\"Edit\" onclick=\"edit_wifi($i)\"/>";
			print "<input type=\"image\" src=\"/images/okIcon16.png\" alt=\"OK\" id=\"ok$i\" onclick=\"ok_wifi($i)\" style=\"display:none \"/></td>";

			print "<td><input type=\"image\" src=\"/images/DeleteIcon16.png\" id=\"del$i\" alt=\"Delete\" onclick=\"delete_wifi($i)\"/>";
			print "<input type=\"image\" src=\"/images/cancelIcon16.png\" alt=\"Cancel\" id=\"cancel$i\" onclick=\"cancel_wifi($i)\" style=\"display:none\"/></td>";

			print "<input type=\"hidden\" id=\"oldSSID$i\" value=\"$s\"/>";
			print "</tr>";
			++$i;
		}

	}

	if(0 == $i)
	{                                                                       
		print "<tr><td colspan=\"4\">No saved Wi-Fi networks</td></tr>";
	} 

	print "</tbody></table>";
?>
	</form>
	<script type="text/javascript">
	function add_wifi()
	{
		var newSSID = document.getElementById('newSSID').value;
		var newPass = document.getElementById('newPass').value;
		document.getElementById('updateSSID').value = newSSID;
		document.getElementById('updatePass').value = newPass;
		$("#update_wifi").submit();
	}

	function edit_mode(p_enable, p_wifi)
	{
		document.getElementById('edit' + p_wifi).style.display = p_enable ? 'none' : 'inline-block';
		document.getElementById('del' + p_wifi).style.display = p_enable ? 'none' : 'inline-block';
		document.getElementById('ok' + p_wifi).style.display = p_enable ? 'inline-block' : 'none';
		document.getElementById('cancel' + p_wifi).style.display = p_enable ? 'inline-block' : 'none';
	}

	function ok_wifi(p_wifi)
	{
		var oldSSID = document.getElementById('oldSSID' + p_wifi).value;
		var newPass = document.getElementById('newPass' + p_wifi).value;
		var p = document.getElementById('pass' + p_wifi);
		p.innerHTML = '***';
		edit_mode(false, p_wifi);

		$("#update_wifi").append('<input type="hidden" name="oldSSID" value="' + oldSSID + '"/>');
		document.getElementById('updateSSID').value = oldSSID;
		document.getElementById('updatePass').value = newPass;
		$("#update_wifi").submit();
	}

	function delete_wifi(p_wifi)
	{
		$("#update_wifi").append('<input type="hidden" name="delete" value="1"/>');
		document.getElementById('updateSSID').value = document.getElementById('ssid' + p_wifi).innerHTML;
		$("#update_wifi").submit();
	}

	function cancel_wifi(p_wifi)
	{
		var ssid = document.getElementById('oldSSID' + p_wifi).value;
		document.getElementById('ssid' + p_wifi).innerHTML = ssid;
		document.getElementById('pass' + p_wifi).innerHTML = '***';
		edit_mode(false, p_wifi);
	}

	function edit_wifi(p_wifi)
	{
		var p = document.getElementById('pass' + p_wifi);
		p.innerHTML = '<input type="password" id="newPass' + p_wifi + '" placeholder="Enter Password"/>';
		edit_mode(true, p_wifi);
	}
	</script>
