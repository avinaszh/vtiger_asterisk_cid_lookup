<div id='asterisk_cid_lookup_help' style="margin: 5px 10px;">
<h3>Asterisk CID Lookup module</h3>

<p>
This module can take a calling phone number from the Asterisk PBX,
try to find this number inside Accounts, Users and Contacrs and
return the found entity name when one is found.
</p>

<p>
<h4>Configure authorization</h4>
Add to config.inc.php of VTiger CRM:
<pre>
$asterisk_cid_lookup = array(
  'user' => 'my_user',
  'password' => 'my_password',
);
</pre>
</p>

<p>
<h4>Configure Lookup Source</h4>
FreePBX CallerID Lookup Sources
<pre>
Description: any string
Type: http/https
Caching: no
Host: my_hostname
Port: 80/443
User: my_user
Passord: my_password
Path: modules/AsteriskCIDLookup/AsteriskCIDLookup.php
Query: number=[NUMBER]
</pre>
</p>

</div>
