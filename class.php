<?php

/**
 * @author corbpie
 * @version 1.0
 */
class vultrAPI
{
    private string $api_url = 'https://api.vultr.com/';//API endpoint (Dont change)
    private string $api_key = 'XXXX-XXXX-XXXX';//Put your Vultr api key here
    private int $subid;//Service id set with: setSubid()
    private array $server_create_details = [];

    public function apiKeyHeader(): array
    {
        return array("API-Key: $this->api_key");
    }

    public function doCurl(string $url, string $type = 'GET', bool $return_http_code = false, array $headers = [], array $post_fields = [])
    {
        $crl = curl_init($this->api_url . $url);
        curl_setopt($crl, CURLOPT_CUSTOMREQUEST, $type);
        if ($type == 'POST') {
            curl_setopt($crl, CURLOPT_POST, true);
            if (!empty($post_fields)) {
                curl_setopt($crl, CURLOPT_POSTFIELDS, $post_fields);
            }
        }
        if (!empty($headers)) {
            curl_setopt($crl, CURLOPT_HTTPHEADER, $headers);
        }
        curl_setopt($crl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($crl, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($crl, CURLOPT_TIMEOUT, 30);
        curl_setopt($crl, CURLOPT_ENCODING, 'gzip,deflate');
        curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
        $call_response = curl_exec($crl);
        $http_response_code = curl_getinfo($crl, CURLINFO_HTTP_CODE);
        curl_close($crl);
        if ($return_http_code) {
            return $http_response_code;
        } else {
            if ($http_response_code == 200) {
                return $call_response;//Return data
            } else {
                return array('http_response_code' => $http_response_code);//Call failed
            }
        }
    }

    public function checkSubidSet()
    {
        if (is_null($this->subid)) {
            echo "No subid is set, it is needed to perform this action.";
            exit;
        }
    }

    public function setSubid(string $subid): void
    {
        $this->subid = $subid;
    }

    /*
     * ACCOUNT INFO
     */
    public function listAccountInfo()
    {
        $header = $this->apiKeyHeader();
        return $this->doCurl("v1/account/info", 'GET', false, $header);
    }

    public function accountRemainingCredit(): float
    {
        $data = json_decode($this->listAccountInfo());
        $balance = str_replace('-', '', $data->balance);
        return ($balance - $data->pending_charges);
    }

    /*
     * SERVER ACTIONS
     */
    public function listServers()
    {
        $header = $this->apiKeyHeader();
        return $this->doCurl('v1/server/list', 'GET', false, $header);
    }

    public function listIpv4()
    {
        $this->checkSubidSet();
        $header = $this->apiKeyHeader();
        return $this->doCurl("v1/server/list_ipv4?SUBID={$this->subid}", 'GET', false, $header);
    }

    public function listIpv6()
    {
        $this->checkSubidSet();
        $header = $this->apiKeyHeader();
        return $this->doCurl("v1/server/list_ipv6?SUBID={$this->subid}", 'GET', false, $header);
    }

    public function listNeighbors()
    {
        $this->checkSubidSet();
        $header = $this->apiKeyHeader();
        return $this->doCurl("v1/server/neighbors?SUBID={$this->subid}", 'GET', false, $header);
    }

    public function serverReboot()
    {
        $this->checkSubidSet();
        $header = $this->apiKeyHeader();
        $post = array("SUBID" => $this->subid);
        return $this->doCurl("v1/server/reboot", 'POST', true, $header, $post);
    }

    public function serverStart()
    {
        $this->checkSubidSet();
        $header = $this->apiKeyHeader();
        $post = array("SUBID" => $this->subid);
        return $this->doCurl("v1/server/start", 'POST', true, $header, $post);
    }

    public function serverStop()
    {
        $this->checkSubidSet();
        $header = $this->apiKeyHeader();
        $post = array("SUBID" => $this->subid);
        return $this->doCurl("v1/server/halt", 'POST', true, $header, $post);
    }

    public function serverDestroy()
    {
        $this->checkSubidSet();
        $header = $this->apiKeyHeader();
        $post = array("SUBID" => $this->subid);
        return $this->doCurl("v1/server/destroy", 'POST', true, $header, $post);
    }

    public function serverReinstall()
    {
        $this->checkSubidSet();
        $header = $this->apiKeyHeader();
        $post = array("SUBID" => $this->subid);
        return $this->doCurl("v1/server/reinstall", 'POST', true, $header, $post);
    }

    public function serverSetLabel(string $label)
    {
        $this->checkSubidSet();
        $header = $this->apiKeyHeader();
        $post = array("SUBID" => $this->subid, "label" => $label);
        return $this->doCurl("v1/server/label_set", 'POST', true, $header, $post);
    }

    public function serverSetTag(string $tag)
    {
        $this->checkSubidSet();
        $header = $this->apiKeyHeader();
        $post = array("SUBID" => $this->subid, "tag" => $tag);
        return $this->doCurl("v1/server/tag_set", 'POST', true, $header, $post);
    }

    public function serverGetBW()
    {
        $this->checkSubidSet();
        $header = $this->apiKeyHeader();
        return $this->doCurl("v1/server/bandwidth?SUBID={$this->subid}", 'GET', false, $header);
    }

    public function serverChangeApp(string $app_id)
    {
        $this->checkSubidSet();
        $header = $this->apiKeyHeader();
        $post = array("SUBID" => $this->subid, "APPID" => $app_id);
        return $this->doCurl("v1/server/app_change", 'POST', true, $header, $post);
    }

    public function serverAppChangeList()
    {
        $this->checkSubidSet();
        $header = $this->apiKeyHeader();
        return $this->doCurl("v1/server/app_change_list?SUBID={$this->subid}", 'GET', false, $header);
    }

    public function serverBackupDisable()
    {
        $this->checkSubidSet();
        $header = $this->apiKeyHeader();
        $post = array("SUBID" => $this->subid);
        return $this->doCurl("v1/server/backup_disable", 'POST', true, $header, $post);
    }

    public function serverBackupEnable()
    {
        $this->checkSubidSet();
        $header = $this->apiKeyHeader();
        $post = array("SUBID" => $this->subid);
        return $this->doCurl("v1/server/backup_enable", 'POST', true, $header, $post);
    }

    public function serverBackupSchedule()
    {
        $this->checkSubidSet();
        $header = $this->apiKeyHeader();
        $post = array("SUBID" => $this->subid);
        return $this->doCurl("v1/server/backup_get_schedule", 'POST', false, $header, $post);
    }

    public function serverSetBackupSchedule(string $cron_type, int $hour, int $day_of_week, int $day_of_month)// daily|weekly|monthly|daily_alt_even|daily_alt_odd
    {
        $this->checkSubidSet();
        $header = $this->apiKeyHeader();
        $post = array("SUBID" => $this->subid, "cron_type" => $cron_type, "hour" => $hour, "dow" => $day_of_week, "dom" => $day_of_month);
        return $this->doCurl("v1/server/backup_set_schedule", 'POST', true, $header, $post);
    }

    public function serverCreateIpv4()
    {
        $this->checkSubidSet();
        $header = $this->apiKeyHeader();
        $post = array("SUBID" => $this->subid);
        return $this->doCurl("v1/server/create_ipv4", 'POST', true, $header, $post);
    }

    public function serverDestroyIpv4(string $ip)
    {
        $this->checkSubidSet();
        $header = $this->apiKeyHeader();
        $post = array("SUBID" => $this->subid, "ip" => $ip);
        return $this->doCurl("v1/server/destroy_ipv4", 'POST', true, $header, $post);
    }

    public function serverFirewallGroup(string $firewall_group)
    {
        $this->checkSubidSet();
        $header = $this->apiKeyHeader();
        $post = array("SUBID" => $this->subid, "FIREWALLGROUPID" => $firewall_group);
        return $this->doCurl("v1/server/firewall_group_set", 'POST', true, $header, $post);
    }

    public function serverAppInfo()
    {
        $this->checkSubidSet();
        $header = $this->apiKeyHeader();
        return $this->doCurl("v1/server/get_app_info?SUBID={$this->subid}", 'GET', false, $header);
    }

    public function serverUserData()
    {
        $this->checkSubidSet();
        $header = $this->apiKeyHeader();
        return $this->doCurl("v1/server/get_user_data?SUBID={$this->subid}", 'GET', false, $header);
    }

    public function serverAttachISO(int $iso_id)
    {
        $this->checkSubidSet();
        $header = $this->apiKeyHeader();
        $post = array("SUBID" => $this->subid, "ISOID" => $iso_id);
        return $this->doCurl("v1/server/iso_attach", 'POST', true, $header, $post);
    }

    public function serverDetachISO()
    {
        $this->checkSubidSet();
        $header = $this->apiKeyHeader();
        $post = array("SUBID" => $this->subid);
        return $this->doCurl("v1/server/iso_attach", 'POST', true, $header, $post);
    }

    public function serverISOInfo()
    {
        $this->checkSubidSet();
        $header = $this->apiKeyHeader();
        return $this->doCurl("v1/server/iso_status?SUBID={$this->subid}", 'GET', false, $header);
    }

    public function serverOSChange(int $os_id)
    {
        $this->checkSubidSet();
        $header = $this->apiKeyHeader();
        $post = array("SUBID" => $this->subid, "OSID" => $os_id);
        return $this->doCurl("v1/server/os_change", 'POST', true, $header, $post);
    }

    public function serverOSChangeList()
    {
        $this->checkSubidSet();
        $header = $this->apiKeyHeader();
        return $this->doCurl("v1/server/os_change_list?SUBID={$this->subid}", 'GET', false, $header);
    }

    public function serverPrivateNetworkDisable(string $network_id)
    {
        $this->checkSubidSet();
        $header = $this->apiKeyHeader();
        $post = array("SUBID" => $this->subid, "NETWORKID" => $network_id);
        return $this->doCurl("v1/server/private_network_disable", 'POST', true, $header, $post);
    }

    public function serverPrivateNetworkEnable(string $network_id)
    {
        $this->checkSubidSet();
        $header = $this->apiKeyHeader();
        $post = array("SUBID" => $this->subid, "NETWORKID" => $network_id);
        return $this->doCurl("v1/server/private_network_enable", 'POST', true, $header, $post);
    }

    public function serverListPrivateNetworks()
    {
        $this->checkSubidSet();
        $header = $this->apiKeyHeader();
        return $this->doCurl("v1/server/private_networks?SUBID={$this->subid}", 'GET', false, $header);
    }

    public function serverRestoreBackup(string $backup_id)
    {
        $this->checkSubidSet();
        $header = $this->apiKeyHeader();
        $post = array("SUBID" => $this->subid, "BACKUPID" => $backup_id);
        return $this->doCurl("v1/server/restore_backup", 'POST', true, $header, $post);
    }

    public function serverRestoreSnapshot(string $snapshot_id)
    {
        $this->checkSubidSet();
        $header = $this->apiKeyHeader();
        $post = array("SUBID" => $this->subid, "SNAPSHOTID" => $snapshot_id);
        return $this->doCurl("v1/server/restore_snapshot", 'POST', true, $header, $post);
    }

    public function serverUpgradablePlans()
    {
        $this->checkSubidSet();
        $header = $this->apiKeyHeader();
        return $this->doCurl("v1/server/upgrade_plan_list?SUBID={$this->subid}", 'GET', false, $header);
    }

    public function serverUpgradePlan(int $vpsplan_id)
    {
        $this->checkSubidSet();
        $header = $this->apiKeyHeader();
        $post = array("SUBID" => $this->subid, "VPSPLANID" => $vpsplan_id);
        return $this->doCurl("v1/server/upgrade_plan", 'POST', true, $header, $post);
    }

    public function serverReverseDefaultIpv4(string $ip)
    {
        $this->checkSubidSet();
        $header = $this->apiKeyHeader();
        $post = array("SUBID" => $this->subid, "ip" => $ip);
        return $this->doCurl("v1/server/reverse_default_ipv4", 'POST', true, $header, $post);
    }

    public function serverReverseDefaultIpv6(string $ip)
    {
        $this->checkSubidSet();
        $header = $this->apiKeyHeader();
        $post = array("SUBID" => $this->subid, "ip" => $ip);
        return $this->doCurl("v1/server/reverse_delete_ipv6", 'POST', true, $header, $post);
    }

    public function serverListReverseIpv6()
    {
        $this->checkSubidSet();
        $header = $this->apiKeyHeader();
        return $this->doCurl("v1/server/reverse_list_ipv6?SUBID={$this->subid}", 'GET', false, $header);
    }

    public function serverSetReverseIpv4(string $ip, string $entry)
    {
        $this->checkSubidSet();
        $header = $this->apiKeyHeader();
        $post = array("SUBID" => $this->subid, "ip" => $ip, "entry" => $entry);
        return $this->doCurl("v1/server/reverse_set_ipv4", 'POST', true, $header, $post);
    }

    public function serverSetReverseIpv6(string $ip, string $entry)
    {
        $this->checkSubidSet();
        $header = $this->apiKeyHeader();
        $post = array("SUBID" => $this->subid, "ip" => $ip, "entry" => $entry);
        return $this->doCurl("v1/server/reverse_set_ipv6", 'POST', true, $header, $post);
    }

    public function serverSetUserData(string $ip, string $user_data)
    {
        $this->checkSubidSet();
        $header = $this->apiKeyHeader();
        $post = array("SUBID" => $this->subid, "ip" => $ip, "userdata" => $user_data);
        return $this->doCurl("v1/server/set_user_data", 'POST', true, $header, $post);
    }

    /*
     * SERVER CREATE BUILD:
     */
    public function serverCreateDC(int $dc_id)
    {
        $this->server_create_details = array(
            "DCID" => $dc_id
        );
    }

    public function serverCreatePlan(int $plan_id)
    {
        $this->server_create_details = array_merge($this->server_create_details, array(
            "VPSPLANID" => $plan_id
        ));
    }

    public function serverCreateType(string $type = 'OS', string $type_id = '1')
    {
        if ($type == 'OS') {
            $this->server_create_details = array_merge($this->server_create_details, array(
                "OSID" => $type_id
            ));
        } elseif ($type == 'SNAPSHOT') {
            $this->server_create_details = array_merge($this->server_create_details, array(
                "OSID" => 164,
                "SNAPSHOTID" => $type_id
            ));
        } elseif ($type == 'ISO') {
            $this->server_create_details = array_merge($this->server_create_details, array(
                "OSID" => 159,
                "ISOID" => $type_id
            ));
        } elseif ($type == 'APP') {
            $this->server_create_details = array_merge($this->server_create_details, array(
                "OSID" => 186,
                "APPID" => $type_id
            ));
        }
    }

    public function serverCreateLabel(string $label)
    {
        $this->server_create_details = array_merge($this->server_create_details, array(
            "label" => $label
        ));
    }

    public function serverCreateHostname(string $hostname)
    {
        $this->server_create_details = array_merge($this->server_create_details, array(
            "hostname " => $hostname
        ));
    }

    public function serverCreateWithIpv4(string $ipv4)
    {
        $this->server_create_details = array_merge($this->server_create_details, array(
            "reserved_ip_v4 " => $ipv4
        ));
    }

    public function serverCreateEnableIpv6(string $ipv6 = 'yes')
    {
        $this->server_create_details = array_merge($this->server_create_details, array(
            "enable_ipv6 " => $ipv6
        ));
    }

    public function serverCreateEnablePrivateNetwork(string $pn = 'yes')
    {
        $this->server_create_details = array_merge($this->server_create_details, array(
            "enable_private_network " => $pn
        ));
    }

    public function serverCreateStartScript(int $script_id)
    {
        $this->server_create_details = array_merge($this->server_create_details, array(
            "SCRIPTID " => $script_id
        ));
    }

    public function serverCreateIPXEURL(string $url)
    {
        $this->server_create_details = array_merge($this->server_create_details, array(
            "ipxe_chain_url " => $url
        ));
    }

    public function serverCreateEnableDDOSProtection(string $ddos_protection = 'yes')
    {
        $this->server_create_details = array_merge($this->server_create_details, array(
            "ddos_protection " => $ddos_protection
        ));
    }

    public function serverCreateOptions()
    {//Shows create server options
        echo 'serverCreateDC(int $dc_id)<br>';
        echo 'serverCreatePlan(int $plan_id)<br>';
        echo 'serverCreateType(string $type, string $type_id)<br>';
        echo '<b>end of required-----------------------------------</b><br>';
        echo '<b>These are optional:</b><br>';
        echo 'serverCreateLabel(string $label)<br>';
        echo 'serverCreateHostname(string $hostname)<br>';
        echo 'serverCreateWithIpv4(string $ipv4)<br>';
        echo 'serverCreateEnableIpv6(string $ipv6 = "yes")<br>';
        echo 'serverCreateEnablePrivateNetwork(string $pn = "yes")<br>';
        echo 'serverCreateStartScript(int $script_id)<br>';
        echo 'serverCreateIPXEURL(string $url)<br>';
        echo 'serverCreateEnableDDOSProtection(string $ddos_protection = "yes")<br>';
        echo '<b>The built array:</b><br>';
        echo 'returnServerCreateArray()<br>';
        echo '<b>Create an instance with the built array:</b><br>';
        echo 'serverCreate(array $this->returnServerCreateArray())<br>';
    }


    public function returnServerCreateArray()
    {
        return $this->server_create_details;
    }

    public function serverCreate()
    {
        $post_options = $this->server_create_details;
        $header = $this->apiKeyHeader();
        return $this->doCurl("v1/server/create", 'POST', false, $header, $post_options);
    }

    /*
     * BACKUPS
     */
    public function listBackups()
    {
        $header = $this->apiKeyHeader();
        return $this->doCurl("v1/backup/list", 'GET', false, $header);
    }

    /*
     * SNAPSHOTS
     */
    public function listSnapshots()
    {
        $header = $this->apiKeyHeader();
        return $this->doCurl("v1/snapshot/list", 'GET', false, $header);
    }

    public function createSnapshot()
    {
        $this->checkSubidSet();
        $header = $this->apiKeyHeader();
        $post = array("SUBID" => $this->subid);
        return $this->doCurl("v1/snapshot/create", 'POST', false, $header, $post);
    }

    public function destroySnapshot(int $snapshot_id)
    {
        $this->checkSubidSet();
        $header = $this->apiKeyHeader();
        $post = array("SNAPSHOTID" => $snapshot_id);
        return $this->doCurl("v1/snapshot/destroy", 'POST', true, $header, $post);
    }

    public function createSnapshotFromURL(string $url)
    {
        $header = $this->apiKeyHeader();
        $post = array("url" => $url);
        return $this->doCurl("v1/snapshot/create_from_url", 'POST', false, $header, $post);
    }

    /*
     * STARTUP SCRIPTS
     */
    public function createStartupScript(string $script_name, string $script)
    {
        $header = $this->apiKeyHeader();
        $post = array("name" => $script_name, "script" => $script);
        return $this->doCurl("v1/startupscript/create", 'POST', false, $header, $post);
    }

    public function destroyStartupScript(string $script_id)
    {
        $header = $this->apiKeyHeader();
        $post = array("SCRIPTID" => $script_id);
        return $this->doCurl("v1/startupscript/destroy", 'POST', true, $header, $post);
    }

    public function updateStartupScript(string $script_id, string $name, string $script)
    {
        $header = $this->apiKeyHeader();
        $post = array("SCRIPTID" => $script_id, "name" => $name, "script" => $script);
        return $this->doCurl("v1/startupscript/update", 'POST', true, $header, $post);
    }

    public function listStartupScripts()
    {
        $header = $this->apiKeyHeader();
        return $this->doCurl("v1/startupscript/list", 'GET', false, $header);
    }

    /*
     * SSH KEY
     */
    public function createSSHKey(string $key_name, string $key)
    {
        $header = $this->apiKeyHeader();
        $post = array("name" => $key_name, "ssh_key" => $key);
        return $this->doCurl("v1/sshkey/create", 'POST', false, $header, $post);
    }

    public function destroySSHKey(string $ssh_key_id)
    {
        $header = $this->apiKeyHeader();
        $post = array("SSHKEYID" => $ssh_key_id);
        return $this->doCurl("v1/sshkey/destroy", 'POST', true, $header, $post);
    }

    public function updateSSHKey(string $ssh_key_id, string $key_name, string $key)
    {
        $header = $this->apiKeyHeader();
        $post = array("SSHKEYID" => $ssh_key_id, "name" => $key_name, "ssh_key" => $key);
        return $this->doCurl("v1/sshkey/update", 'POST', true, $header, $post);
    }

    public function listSSHKeys()
    {
        $header = $this->apiKeyHeader();
        return $this->doCurl("v1/sshkey/list", 'GET', false, $header);
    }

    /*
     * RESERVED IP
     */
    public function listReservedIps()
    {
        $header = $this->apiKeyHeader();
        return $this->doCurl("v1/reservedip/list", 'GET', false, $header);
    }

    public function attachIp(string $ip_address, string $subid)
    {
        $this->checkSubidSet();
        $header = $this->apiKeyHeader();
        $post = array("attach_SUBID" => $subid, "ip_address" => $ip_address);
        return $this->doCurl("v1/reservedip/attach", 'POST', true, $header, $post);
    }

    public function convertIp(string $ip_address, string $subid)
    {
        $this->checkSubidSet();
        $header = $this->apiKeyHeader();
        $post = array("SUBID" => $subid, "ip_address" => $ip_address);
        return $this->doCurl("v1/reservedip/convert", 'POST', false, $header, $post);
    }

    public function createIp(string $ip_type, int $dcid, string $label)
    {
        $header = $this->apiKeyHeader();
        $post = array("DCID" => $dcid, "ip_type" => $ip_type, "label" => $label);
        return $this->doCurl("v1/reservedip/create", 'POST', false, $header, $post);
    }

    public function destroyIp(string $ip_address)
    {
        $header = $this->apiKeyHeader();
        $post = array("ip_address" => $ip_address);
        return $this->doCurl("v1/reservedip/destroy", 'POST', true, $header, $post);
    }

    public function detachIp(string $ip_address, string $subid)
    {
        $this->checkSubidSet();
        $header = $this->apiKeyHeader();
        $post = array("ip_address" => $ip_address, "detach_SUBID" => $subid);
        return $this->doCurl("v1/reservedip/detach", 'POST', true, $header, $post);
    }

    /*
     * ISO IMAGES
     */
    public function listISOs()
    {
        $header = $this->apiKeyHeader();
        return $this->doCurl("v1/iso/list", 'GET', false, $header);
    }

    public function listPublicISOs()
    {
        $header = $this->apiKeyHeader();
        return $this->doCurl("v1/iso/list_public", 'GET', false, $header);
    }

    public function uploadISO(string $iso_url)
    {
        $header = $this->apiKeyHeader();
        $post = array("url" => $iso_url);
        return $this->doCurl("v1/iso/create_from_url", 'POST', false, $header, $post);
    }

    public function destroyISO(string $iso_id)
    {
        $header = $this->apiKeyHeader();
        $post = array("ISOID" => $iso_id);
        return $this->doCurl("v1/iso/destroy", 'POST', true, $header, $post);
    }

    /*
     * BLOCK STORAGE
     */
    public function listBlockStorage()
    {
        $header = $this->apiKeyHeader();
        return $this->doCurl("v1/block/list", 'GET', false, $header);
    }

    public function createBlockStorage(string $dc_id, int $size_gb, string $label)
    {
        $header = $this->apiKeyHeader();
        $post = array("DCID" => $dc_id, "size_gb" => $size_gb, "label" => $label);
        return $this->doCurl("v1/block/create", 'POST', false, $header, $post);
    }

    public function attachBlockStorage(string $block_id)
    {
        $this->checkSubidSet();
        $header = $this->apiKeyHeader();
        $post = array("SUBID" => $block_id, "attach_to_SUBID" => $this->subid);
        return $this->doCurl("v1/block/attach", 'POST', true, $header, $post);
    }

    public function deleteBlockStorage(string $block_id)
    {
        $header = $this->apiKeyHeader();
        $post = array("SUBID" => $block_id);
        return $this->doCurl("v1/block/delete", 'POST', true, $header, $post);
    }

    public function detachBlockStorage(string $block_id)
    {
        $this->checkSubidSet();
        $header = $this->apiKeyHeader();
        $post = array("SUBID" => $block_id);
        return $this->doCurl("v1/block/detach", 'POST', true, $header, $post);
    }

    public function labelBlockStorage(string $block_id, string $label)
    {
        $header = $this->apiKeyHeader();
        $post = array("SUBID" => $block_id, "label" => $label);
        return $this->doCurl("v1/block/label_set", 'POST', true, $header, $post);
    }

    public function resizeBlockStorage(string $block_id, int $new_size_gb)
    {
        $header = $this->apiKeyHeader();
        $post = array("SUBID" => $block_id, "size_gb" => $new_size_gb);
        return $this->doCurl("v1/block/resize", 'POST', true, $header, $post);
    }

    /*
     * DNS
     */
    public function listDNS()
    {
        $header = $this->apiKeyHeader();
        return $this->doCurl("v1/dns/list", 'GET', false, $header);
    }

    public function dnsCreateDomain(string $domain, string $server_ip)
    {
        $header = $this->apiKeyHeader();
        $post = array("domain" => $domain, "serverip" => $server_ip);
        return $this->doCurl("v1/dns/create_domain", 'POST', true, $header, $post);
    }

    public function dnsCreateRecord(string $domain, string $name, string $type, string $data)
    {
        $header = $this->apiKeyHeader();
        $post = array("domain" => $domain, "name" => $name, "type" => $type, "data" => $data);
        return $this->doCurl("v1/dns/create_record", 'POST', true, $header, $post);
    }

    public function dnsDeleteDomain(string $domain)
    {
        $header = $this->apiKeyHeader();
        $post = array("domain" => $domain);
        return $this->doCurl("v1/dns/delete_domain", 'POST', true, $header, $post);
    }

    public function dnsDeleteRecord(string $domain, string $record_id)
    {
        $header = $this->apiKeyHeader();
        $post = array("domain" => $domain, "RECORDID" => $record_id);
        return $this->doCurl("v1/dns/delete_record", 'POST', true, $header, $post);
    }

    public function dnsEnableDNSSEC(string $domain, string $enable = 'yes')
    {
        $header = $this->apiKeyHeader();
        $post = array("domain" => $domain, "enable" => $enable);
        return $this->doCurl("v1/dns/dnssec_enable", 'POST', true, $header, $post);
    }

    public function dnsUpdateSOA(string $domain, string $nsprimary, string $email)
    {
        $header = $this->apiKeyHeader();
        $post = array("domain" => $domain, "nsprimary" => $nsprimary, "email" => $email);
        return $this->doCurl("v1/dns/soa_update", 'POST', true, $header, $post);
    }

    public function dnsUpdateRecord(string $domain, string $record_id, string $name, string $type, string $data)
    {
        $header = $this->apiKeyHeader();
        $post = array("domain" => $domain, "RECORDID" => $record_id, "name" => $name, "type" => $type, "data" => $data);
        return $this->doCurl("v1/dns/update_record", 'POST', true, $header, $post);
    }

    public function dnsSOAINFO($domain)
    {
        $header = $this->apiKeyHeader();
        return $this->doCurl("v1/dns/soa_info?domain=$domain", 'GET', false, $header);
    }

    public function dnsListRecordsDomain($domain)
    {
        $header = $this->apiKeyHeader();
        return $this->doCurl("v1/dns/records?domain=$domain", 'GET', false, $header);
    }

    public function dnsDNSSECInfo($domain)
    {
        $header = $this->apiKeyHeader();
        return $this->doCurl("v1/dns/dnssec_info?domain=$domain", 'GET', false, $header);
    }

    /*
     * PLANS
     */
    public function listPlans(string $type = 'all')// all|vc2|ssd|vdc2|dedicated|vc2z
    {
        return $this->doCurl("v1/plans/list?type=$type", 'GET', false);
    }

    public function listBareMetalPlans()
    {
        return $this->doCurl("v1/plans/list_baremetal", 'GET', false);
    }

    public function listVC2Plans()
    {
        return $this->doCurl("v1/plans/list_vc2", 'GET', false);
    }

    public function listVC2ZPlans()
    {
        return $this->doCurl("v1/plans/list_vc2z", 'GET', false);
    }

    public function listVDC2Plans()
    {
        return $this->doCurl("v1/plans/list_vdc2", 'GET', false);
    }

    /*
     * REGIONS
     */
    public function listRegions(string $available = 'yes')// List regions that only have plans available
    {
        return $this->doCurl("v1/regions/list?availability$available", 'GET', false);
    }

    public function regionAvailability(int $dc_id, string $type = 'all')// all|vc2|ssd|vdc2|dedicated|vc2z
    {
        return $this->doCurl("v1/regions/availability?DCID=$dc_id&type=$type", 'GET', false);
    }

    public function regionAvailabilityBareMetal(int $dc_id)
    {
        return $this->doCurl("v1/regions/availability_baremetal?DCID=$dc_id", 'GET', false);
    }

    public function regionAvailabilityVC2(int $dc_id)
    {
        return $this->doCurl("v1/regions/availability_vc2?DCID=$dc_id", 'GET', false);
    }

    public function regionAvailabilityVDC2(int $dc_id)
    {
        return $this->doCurl("v1/regions/availability_vdc2?DCID=$dc_id", 'GET', false);
    }

    /*
     * API INFO
     */
    public function apiInfo()
    {
        return $this->doCurl("v1/auth/info", 'GET', false);
    }

    /*
     * OPERATING SYSTEMS
     */
    public function listOS()
    {
        return $this->doCurl("v1/os/list", 'GET', false);
    }

    /*
     * APPLICATIONS
     */
    public function listApps()
    {
        return $this->doCurl("v1/app/list", 'GET', false);
    }

    /*
     * USER MANAGEMENT
     */
    public function listUsers()
    {
        $header = $this->apiKeyHeader();
        return $this->doCurl("v1/user/list", 'GET', false, $header);
    }

    public function createUser(string $email, string $name, string $password)
    {
        $header = $this->apiKeyHeader();
        $post = array("email" => $email, "name" => $name, "password" => $password);
        return $this->doCurl("v1/user/create ", 'POST', false, $header, $post);
    }

    public function deleteUser(string $user_id)
    {
        $header = $this->apiKeyHeader();
        $post = array("USERID" => $user_id);
        return $this->doCurl("v1/user/delete ", 'POST', true, $header, $post);
    }

    public function updateUser(string $user_id, string $email, string $name, string $password)
    {
        $header = $this->apiKeyHeader();
        $post = array("USERID" => $user_id, "email" => $email, "name" => $name, "password" => $password);
        return $this->doCurl("v1/user/update ", 'POST', true, $header, $post);
    }

    /*
     * OBJECT STORAGE
     */
    public function listObjectStorage()
    {
        $header = $this->apiKeyHeader();
        return $this->doCurl("v1/objectstorage/list", 'GET', false, $header);
    }

    public function listObjectStorageCluster()
    {
        return $this->doCurl("v1/objectstorage/list_cluster", 'GET', false);
    }

    public function createObjectStorage(int $cluster_id, string $label)
    {
        $header = $this->apiKeyHeader();
        $post = array("OBJSTORECLUSTERID" => $cluster_id, "label" => $label);
        return $this->doCurl("v1/objectstorage/create ", 'POST', false, $header, $post);
    }

    public function deleteObjectStorage(int $obj_id)
    {
        $header = $this->apiKeyHeader();
        $post = array("SUBID" => $obj_id);
        return $this->doCurl("v1/objectstorage/destroy ", 'POST', true, $header, $post);
    }

    public function labelObjectStorage(string $label, int $obj_id)
    {
        $header = $this->apiKeyHeader();
        $post = array("SUBID" => $obj_id, "label" => $label);
        return $this->doCurl("v1/objectstorage/label_set ", 'POST', true, $header, $post);
    }

    public function s3keyRegenObjectStorage(int $obj_id, string $key)
    {
        $header = $this->apiKeyHeader();
        $post = array("SUBID" => $obj_id, "s3_access_key" => $key);
        return $this->doCurl("v1/objectstorage/s3key_regenerate ", 'POST', false, $header, $post);
    }

    /*
     * HELPER FUNCTIONS
    */
    public function convertBytes(int $bytes, string $convert_to = 'GB', bool $format = true, int $decimals = 2)
    {
        if ($convert_to == 'GB') {
            $value = ($bytes / 1073741824);
        } elseif ($convert_to == 'MB') {
            $value = ($bytes / 1048576);
        } elseif ($convert_to == 'KB') {
            $value = ($bytes / 1024);
        } else {
            $value = $bytes;
        }
        if ($format) {
            return number_format($value, $decimals);
        } else {
            return $value;
        }
    }

    public function boolToInt(bool $bool): int
    {
        ($bool) ? $int = 1 : $int = 0;
        return $int;
    }

    public function saveOutput(string $save_as, $output)
    {
        file_put_contents($save_as, $output);
    }

    public function responseAsString(int $http_code)
    {
        if ($http_code == 200) {
            return 'Success';
        } else {
            return 'Failed';
        }
    }
//END OF CLASS
}