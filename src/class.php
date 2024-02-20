<?php

namespace Corbpie\VultrAPIv2;

class VultrAPI
{
    protected const API_URL = 'https://api.vultr.com/';//API endpoint (Dont change)
    protected const API_KEY = 'XYZ-ABC-123';//Put your Vultr API key here
    protected int $instance_id;//Service id set with: setSubId()
    protected array $server_create_details = [];

    protected bool $requires_sub_id = false;

    public function apiKeyHeader(): array
    {
        return ["Authorization: Bearer " . self::API_KEY, "Content-Type: application/json"];
    }

    public function doCall(string $url, string $type = 'GET', bool $return_http_code = false, array $headers = [], array $post_fields = [])
    {
        if ($this->requires_sub_id && !isset($this->instance_id)) {
            return ["No sub id is set, it is needed to perform this action."];
        }
        $crl = curl_init(self::API_URL . $url);
        curl_setopt($crl, CURLOPT_CUSTOMREQUEST, $type);
        if ($type === 'POST') {
            curl_setopt($crl, CURLOPT_POST, true);
            if (!empty($post_fields)) {
                curl_setopt($crl, CURLOPT_POSTFIELDS, json_encode($post_fields));
            }
        } elseif ($type === 'PATCH') {
            curl_setopt($crl, CURLOPT_CUSTOMREQUEST, 'PATCH');
            curl_setopt($crl, CURLOPT_POSTFIELDS, json_encode($post_fields));
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
        }
        if ($http_response_code === 200 || $http_response_code === 201 || $http_response_code === 202) {
            return $this->call_data = $call_response;//Return data
        }
        return $this->call_data = ['http_response_code' => $http_response_code];//Call failed
    }

    public function setSubId(string $instance_id): void
    {
        $this->instance_id = $instance_id;
    }

    /*
     * ACCOUNT INFO
     */
    public function listAccountInfo()
    {
        return $this->doCall("v2/account", "GET", false, $this->apiKeyHeader());
    }

    public function accountRemainingCredit(): float
    {
        $data = json_decode($this->listAccountInfo());
        return (str_replace('-', '', $data->balance) - $data->pending_charges);
    }

    /*
     * SERVER ACTIONS
     */
    public function listServers()
    {
        return $this->doCall("v2/instances", "GET", false, $this->apiKeyHeader());
    }

    public function listServer()
    {
        $this->requires_sub_id = true;
        return $this->doCall("v2/instances/$this->instance_id", "GET", false, $this->apiKeyHeader());
    }

    public function listIpv4()
    {
        $this->requires_sub_id = true;
        return $this->doCall("v2/instances/$this->instance_id/ipv4", 'GET', false, $this->apiKeyHeader());
    }

    public function listIpv6()
    {
        $this->requires_sub_id = true;
        return $this->doCall("v2/instances/$this->instance_id/ipv6", 'GET', false, $this->apiKeyHeader());
    }

    public function listNeighbors()
    {
        $this->requires_sub_id = true;
        return $this->doCall("v2/instances/$this->instance_id/neighbors", 'GET', false, $this->apiKeyHeader());
    }

    public function instanceReboot()
    {
        $this->requires_sub_id = true;
        return $this->doCall("v2/instances/$this->instance_id/reboot", 'POST', true, $this->apiKeyHeader());
    }

    public function instanceStart()
    {
        $this->requires_sub_id = true;
        return $this->doCall("v2/instances/$this->instance_id/start", 'POST', true, $this->apiKeyHeader());
    }

    public function serverStop()
    {
        $this->requires_sub_id = true;
        return $this->doCall("v2/instances/$this->instance_id/halt", 'POST', true, $this->apiKeyHeader());
    }

    public function instanceDestroy()
    {
        $this->requires_sub_id = true;
        return $this->doCall("v2/instances/$this->instance_id", 'DELETE', true, $this->apiKeyHeader());
    }

    public function instanceUpdate(array $values = [])
    {
        $this->requires_sub_id = true;
        return $this->doCall("v2/instances/$this->instance_id", 'PATCH', true, $this->apiKeyHeader(), $values);
    }

    public function instanceReinstall(string $hostname = '')
    {
        $this->requires_sub_id = true;
        if (!empty($hostname)) {
            $post = ["hostname" => $hostname];
        } else {
            $post = [];
        }
        return $this->doCall("v2/instances/$this->instance_id/reinstall", 'POST', true, $this->apiKeyHeader(), $post);
    }

    public function instanceSetLabel(string $label)
    {
        $this->requires_sub_id = true;
        return $this->doCall("v2/instances/$this->instance_id", 'PATCH', true, $this->apiKeyHeader(), ['label' => $label]);
    }

    public function instanceSetTag(string $tag)
    {
        $this->requires_sub_id = true;
        return $this->doCall("v2/instances/$this->instance_id", 'PATCH', true, $this->apiKeyHeader(), ['tag' => $tag]);
    }

    public function instanceGetBW()
    {
        $this->requires_sub_id = true;
        return $this->doCall("v2/instances/$this->instance_id/bandwidth", 'GET', false, $this->apiKeyHeader());
    }

    public function instanceChangeApp(int $app_id)
    {
        $this->requires_sub_id = true;
        return $this->doCall("v2/instances/$this->instance_id", 'PATCH', true, $this->apiKeyHeader(), ['app_id' => $app_id]);
    }

    public function instanceBackupDisable()
    {
        $this->requires_sub_id = true;
        return $this->doCall("v2/instances/$this->instance_id", 'PATCH', true, $this->apiKeyHeader(), ['backups' => 'disable']);
    }

    public function instanceBackupEnable()
    {
        $this->requires_sub_id = true;
        return $this->doCall("v2/instances/$this->instance_id", 'PATCH', true, $this->apiKeyHeader(), ['backups' => 'enable']);
    }

    public function instanceBackupSchedule()
    {
        $this->requires_sub_id = true;
        return $this->doCall("v2/instances/$this->instance_id/backup-schedule", 'GET', false, $this->apiKeyHeader());
    }

    public function instanceSetBackupSchedule(string $cron_type, int $hour, int $day_of_week, int $day_of_month)// daily|weekly|monthly|daily_alt_even|daily_alt_odd
    {
        $this->requires_sub_id = true;
        $post = ["type" => $cron_type, "hour" => $hour, "dow" => $day_of_week, "dom" => $day_of_month];
        return $this->doCall("v2/instances/$this->instance_id/backup-schedule", 'POST', false, $this->apiKeyHeader(), $post);
    }

    public function instanceCreateIpv4(bool $reboot = false)
    {
        $this->requires_sub_id = true;
        return $this->doCall("v2/instance/$this->instance_id/ipv4", 'POST', false, $this->apiKeyHeader(), ['reboot' => $reboot]);
    }

    public function instanceDestroyIpv4(string $ip)
    {
        $this->requires_sub_id = true;
        return $this->doCall("v2/instance/$this->instance_id/ipv4/$ip", 'DELETE', true, $this->apiKeyHeader());
    }

    public function instanceFirewallGroup(string $firewall_group_id)
    {
        $this->requires_sub_id = true;
        return $this->doCall("v2/instances/$this->instance_id", 'PATCH', true, $this->apiKeyHeader(), ['firewall_group_id' => $firewall_group_id]);
    }

    public function instanceUserData()
    {
        $this->requires_sub_id = true;
        return $this->doCall("v2/instances/$this->instance_id/user-data", 'GET', false, $this->apiKeyHeader());
    }

    public function instanceAttachISO(string $iso_id)
    {
        $this->requires_sub_id = true;
        return $this->doCall("v2/instances/$this->instance_id/iso/attach", 'POST', false, $this->apiKeyHeader(), ['iso_id' => $iso_id]);
    }

    public function instanceDetachISO()
    {
        $this->requires_sub_id = true;
        return $this->doCall("v2/instances/$this->instance_id/iso/detach", 'POST', false, $this->apiKeyHeader());
    }

    public function instanceISOInfo()
    {
        $this->requires_sub_id = true;
        return $this->doCall("v2/instances/$this->instance_id/iso", 'GET', false, $this->apiKeyHeader());
    }

    public function instanceOSChange(string $os_id)
    {
        $this->requires_sub_id = true;
        return $this->doCall("v2/instances/$this->instance_id", 'PATCH', true, $this->apiKeyHeader(), ['os_id' => $os_id]);
    }

    public function instanceOSChangeList()
    {
        $this->requires_sub_id = true;
        return $this->doCall("v2/instances/$this->instance_id/upgrades", 'GET', false, $this->apiKeyHeader(), ['type' => 'os']);
    }

    public function instancePrivateNetworkAttach(string $network_id)
    {
        $this->requires_sub_id = true;
        return $this->doCall("v2/instances/$this->instance_id/private-networks/attach", 'POST', true, $this->apiKeyHeader(), ['network_id' => $network_id]);
    }

    public function instancePrivateNetworkDetach(string $network_id)
    {
        $this->requires_sub_id = true;
        return $this->doCall("v2/instances/$this->instance_id/private-networks/detach", 'POST', true, $this->apiKeyHeader(), ['network_id' => $network_id]);
    }

    public function instancePrivateNetworkDisable(string $network_id)
    {
        $this->requires_sub_id = true;
        return $this->doCall("v2/instances/$this->instance_id", 'PATCH', true, $this->apiKeyHeader(), ['enable_private_network' => false]);
    }

    public function instancePrivateNetworkEnable(string $network_id)
    {
        $this->requires_sub_id = true;
        return $this->doCall("v2/instances/$this->instance_id", 'PATCH', true, $this->apiKeyHeader(), ['enable_private_network' => true]);
    }

    public function instanceListPrivateNetworks()
    {
        $this->requires_sub_id = true;
        return $this->doCall("v2/instances/$this->instance_id/private-networks", 'GET', false, $this->apiKeyHeader());
    }

    public function instanceRestoreBackup(string $backup_id)
    {
        $this->requires_sub_id = true;
        $post = ["backup_id" => $backup_id];
        return $this->doCall("v2/instances/$this->instance_id/restore", 'POST', true, $this->apiKeyHeader(), $post);
    }

    public function instanceRestoreSnapshot(string $snapshot_id)
    {
        $this->requires_sub_id = true;
        $post = ["snapshot_id" => $snapshot_id];
        return $this->doCall("v2/instances/$this->instance_id/restore", 'POST', true, $this->apiKeyHeader(), $post);
    }

    public function instanceUpgradePlan(string $vpsplan_id)
    {
        $this->requires_sub_id = true;
        return $this->doCall("v2/instances/$this->instance_id", 'PATCH', true, $this->apiKeyHeader(), ['plan' => $vpsplan_id]);
    }

    public function instanceSetReverseIpv4(string $ip, string $reverse)
    {
        $this->requires_sub_id = true;
        return $this->doCall("v2/instances/$this->instance_id/ipv4/reverse", 'POST', true, $this->apiKeyHeader(), ["ip" => $ip, "reverse" => $reverse]);
    }

    public function instanceSetReverseIpv6(string $ip, string $reverse)
    {
        $this->requires_sub_id = true;
        return $this->doCall("v2/instances/$this->instance_id/ipv6/reverse", 'POST', true, $this->apiKeyHeader(), ["ip" => $ip, "reverse" => $reverse]);
    }

    public function instanceListReverseIpv4()
    {
        $this->requires_sub_id = true;
        return $this->doCall("v2/instances/$this->instance_id/ipv4/reverse", 'GET', false, $this->apiKeyHeader());
    }

    public function instanceListReverseIpv6()
    {
        $this->requires_sub_id = true;
        return $this->doCall("v2/instances/$this->instance_id/ipv6/reverse", 'GET', false, $this->apiKeyHeader());
    }

    public function instanceDeleteReverseIpv4(string $ip)
    {
        $this->requires_sub_id = true;
        return $this->doCall("v2/instances/$this->instance_id/ipv4/reverse/$ip", 'DELETE', false, $this->apiKeyHeader());
    }

    public function instanceDeleteReverseIpv6(string $ip)
    {
        $this->requires_sub_id = true;
        return $this->doCall("v2/instances/$this->instance_id/ipv6/reverse/$ip", 'DELETE', false, $this->apiKeyHeader());
    }

    /*
     * SERVER CREATE BUILD:
     */
    public function serverCreateDC(string $dc_id)
    {
        $this->server_create_details = [
            "region" => $dc_id
        ];
    }

    public function serverCreatePlan(string $plan_id)
    {
        $this->server_create_details = array_merge($this->server_create_details, [
            "plan" => $plan_id
        ]);
    }

    public function serverCreateType(string $type = 'OS', string $type_id = '1'): void
    {
        if ($type === 'OS') {
            $this->server_create_details = array_merge($this->server_create_details, [
                "os_id" => $type_id
            ]);
        } elseif ($type === 'SNAPSHOT') {
            $this->server_create_details = array_merge($this->server_create_details, [
                "snapshot_id" => $type_id
            ]);
        } elseif ($type === 'ISO') {
            $this->server_create_details = array_merge($this->server_create_details, [
                "os_id" => 159,
                "iso_id" => $type_id
            ]);
        } elseif ($type === 'APP') {
            $this->server_create_details = array_merge($this->server_create_details, [
                "os_id" => 186,
                "app_id" => $type_id
            ]);
        }
    }

    public function serverCreateLabel(string $label)
    {
        $this->server_create_details = array_merge($this->server_create_details, [
            "label" => $label
        ]);
    }

    public function serverCreateHostname(string $hostname)
    {
        $this->server_create_details = array_merge($this->server_create_details, [
            "hostname " => $hostname
        ]);
    }

    public function serverCreateWithIpv4(string $ipv4)
    {
        $this->server_create_details = array_merge($this->server_create_details, [
            "reserved_ipv4 " => $ipv4
        ]);
    }

    public function serverCreateEnableIpv6(bool $ipv6 = true)
    {
        $this->server_create_details = array_merge($this->server_create_details, [
            "enable_ipv6 " => $ipv6
        ]);
    }

    public function serverCreateEnablePrivateNetwork(string $pn = 'yes')
    {
        $this->server_create_details = array_merge($this->server_create_details, [
            "enable_private_network " => $pn
        ]);
    }

    public function serverCreateStartScript(int $script_id)
    {
        $this->server_create_details = array_merge($this->server_create_details, [
            "script_id " => $script_id
        ]);
    }

    public function serverCreateIPXEURL(string $url)
    {
        $this->server_create_details = array_merge($this->server_create_details, [
            "ipxe_chain_url " => $url
        ]);
    }


    public function serverEnableBackups(bool $backups = false)
    {
        $this->server_create_details = array_merge($this->server_create_details, [
            "backups " => $backups
        ]);
    }

    public function serverCreateEnableDDOSProtection(string $ddos_protection = 'yes')
    {
        $this->server_create_details = array_merge($this->server_create_details, [
            "ddos_protection " => $ddos_protection
        ]);
    }

    public function serverCreateOptions(): void
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
        echo 'serverEnableBackups(bool $backups)<br>';
        echo 'serverCreateEnableDDOSProtection(string $ddos_protection = "yes")<br>';
        echo '<b>The built array:</b><br>';
        echo 'returnServerCreateArray()<br>';
        echo '<b>Create an instance with the built array:</b><br>';
        echo 'serverCreate(array $this->returnServerCreateArray())<br>';
    }

    public function returnServerCreateArray(): false|string
    {
        return json_encode($this->server_create_details);
    }

    public function serverCreate()
    {
        return $this->doCall("v2/instances", 'POST', false, $this->apiKeyHeader(), $this->server_create_details);
    }

    /*
     * BACKUPS
     */
    public function listBackups()
    {
        return $this->doCall("v2/backups", 'GET', false, $this->apiKeyHeader());
    }

    public function getBackupData(string $backup_id)
    {
        return $this->doCall("v2/backups/$backup_id", 'GET', false, $this->apiKeyHeader());
    }

    /*
     * SNAPSHOTS
     */
    public function listSnapshots()
    {
        return $this->doCall("v2/snapshots", 'GET', false, $this->apiKeyHeader());
    }

    public function getSnapshotData(string $snapshot_id)
    {
        return $this->doCall("v2/snapshots/$snapshot_id", 'GET', false, $this->apiKeyHeader());
    }

    public function createSnapshot(string $desc = 'DESC VAR EMPTY')
    {
        $this->requires_sub_id = true;
        $post = ["instance_id" => $this->instance_id, "description" => $desc];
        return $this->doCall("v2/snapshots", 'POST', false, $this->apiKeyHeader(), $post);
    }

    public function deleteSnapshot(string $snapshot_id)
    {
        return $this->doCall("v2/snapshots/$snapshot_id", 'DELETE', true, $this->apiKeyHeader());
    }

    public function createSnapshotFromURL(string $url)
    {
        return $this->doCall("v2/snapshot/create-from-url", 'POST', false, $this->apiKeyHeader(), ["url" => $url]);
    }

    public function updateSnapshot(string $snapshot_id, string $description)
    {
        return $this->doCall("v2/snapshots/$snapshot_id", 'PUT', false, $this->apiKeyHeader(), ["description" => $description]);
    }

    /*
     * STARTUP SCRIPTS
     */
    public function createStartupScript(string $script_name, string $type, string $script)
    {
        $post = ["name" => $script_name, "type" => $type, "script" => $script];
        return $this->doCall("v2/startup-scripts", 'POST', false, $this->apiKeyHeader(), $post);
    }

    public function destroyStartupScript(string $script_id)
    {
        $post = ["startup-id" => $script_id];
        return $this->doCall("v2/startup-scripts/destroy", 'DELETE', true, $this->apiKeyHeader(), $post);
    }

    public function updateStartupScript(string $script_id, string $name, string $type, string $script)
    {
        $post = ["startup-id" => $script_id, "name" => $name, "type" => $type, "script" => $script];
        return $this->doCall("v2/startup-scripts/update", 'PATCH', true, $this->apiKeyHeader(), $post);
    }

    public function listStartupScripts()
    {
        return $this->doCall("v2/startup-scripts", 'GET', false, $this->apiKeyHeader());
    }

    public function getStartupScriptData(string $script_id)
    {
        return $this->doCall("v2/startup-scripts/$script_id", 'GET', false, $this->apiKeyHeader());
    }

    /*
     * SSH KEY
     */
    public function createSSHKey(string $key_name, string $key)
    {
        $post = ["name" => $key_name, "ssh_key" => $key];
        return $this->doCall("v2/ssh-keys", 'POST', false, $this->apiKeyHeader(), $post);
    }

    public function destroySSHKey(string $ssh_key_id)
    {
        return $this->doCall("v2/ssh-keys/$ssh_key_id", 'DELETE', true, $this->apiKeyHeader());
    }

    public function updateSSHKey(string $ssh_key_id, string $key_name, string $key)
    {
        $post = ["name" => $key_name, "ssh_key" => $key];
        return $this->doCall("v2/ssh-keys/$ssh_key_id", 'PATCH', true, $this->apiKeyHeader(), $post);
    }

    public function listSSHKeys()
    {
        return $this->doCall("v2/ssh-keys", 'GET', false, $this->apiKeyHeader());
    }

    public function getSSHKeyData(string $ssh_key_id)
    {
        return $this->doCall("v2/ssh-keys/$ssh_key_id", 'GET', false, $this->apiKeyHeader());
    }

    /*
     * RESERVED IP
     */
    public function listReservedIps()
    {
        return $this->doCall("v2/reserved-ips", 'GET', false, $this->apiKeyHeader());
    }

    public function attachIp(string $ip_address)
    {
        $this->requires_sub_id = true;
        $post = ["instance_id" => $this->instance_id];
        return $this->doCall("v2/reserved-ips/$ip_address/attach", 'POST', true, $this->apiKeyHeader(), $post);
    }

    public function convertIp(string $ip_address, string $label)
    {
        $this->requires_sub_id = true;
        $post = ["label" => $label, "ip_address" => $ip_address];
        return $this->doCall("v2/reserved-ips/convert", 'POST', false, $this->apiKeyHeader(), $post);
    }

    public function createIp(string $ip_type, string $region, string $label)
    {
        $post = ["region" => $region, "ip_type" => $ip_type, "label" => $label];
        return $this->doCall("v2/reserved-ips", 'POST', false, $this->apiKeyHeader(), $post);
    }

    public function destroyIp(string $ip_address)
    {
        return $this->doCall("v2/reserved-ips/$ip_address", 'DELETE', true, $this->apiKeyHeader());
    }

    public function detachIp(string $ip_address)
    {
        $this->requires_sub_id = true;
        return $this->doCall("v2/reserved-ips/$ip_address/detach", 'POST', true, $this->apiKeyHeader());
    }

    /*
     * ISO IMAGES
     */
    public function listISOs()
    {
        return $this->doCall("v2/iso", 'GET', false, $this->apiKeyHeader());
    }

    public function getISOData(string $iso_id)
    {
        return $this->doCall("v2/iso/$iso_id", 'GET', false, $this->apiKeyHeader());
    }

    public function listPublicISOs()
    {
        return $this->doCall("v2/iso-public", 'GET', false, $this->apiKeyHeader());
    }

    public function uploadISO(string $iso_url)
    {
        return $this->doCall("v2/iso", 'POST', false, $this->apiKeyHeader(), ["url" => $iso_url]);
    }

    public function destroyISO(string $iso_id)
    {
        return $this->doCall("v2/iso/$iso_id", 'DELETE', true, $this->apiKeyHeader());
    }

    /*
     * BLOCK STORAGE
     */
    public function listBlockStorage()
    {
        return $this->doCall("v2/blocks", 'GET', false, $this->apiKeyHeader());
    }

    public function getBlockStorageData(string $block_id)
    {
        return $this->doCall("v2/blocks/$block_id", 'GET', false, $this->apiKeyHeader());
    }

    public function createBlockStorage(string $region_id, int $size_gb, string $label = '')
    {
        $values = ['region' => $region_id, 'size_gb' => $size_gb, 'label' => $label];
        return $this->doCall("v2/blocks", 'POST', false, $this->apiKeyHeader(), $values);
    }

    public function attachBlockStorage(string $block_id, bool $live)
    {
        $this->requires_sub_id = true;
        $post = ["instance_id" => $this->instance_id, "live" => $live];
        return $this->doCall("v2/blocks/$block_id/attach", 'POST', true, $this->apiKeyHeader(), $post);
    }

    public function deleteBlockStorage(string $block_id)
    {
        return $this->doCall("v2/blocks/$block_id", 'DELETE', true, $this->apiKeyHeader());
    }

    public function detachBlockStorage(string $block_id, bool $live = true)
    {
        $post = ["live" => $live];
        return $this->doCall("v2/blocks/$block_id/detach", 'POST', true, $this->apiKeyHeader(), $post);
    }

    public function labelBlockStorage(string $block_id, string $label)
    {
        $post = ["label" => $label];
        return $this->doCall("v2/blocks/$block_id", 'PATCH', true, $this->apiKeyHeader(), $post);
    }

    public function resizeBlockStorage(string $block_id, int $size_gb)
    {
        $post = ["size_gb" => $size_gb];
        return $this->doCall("v2/blocks/$block_id", 'PATCH', true, $this->apiKeyHeader(), $post);
    }

    /*
     * DNS
     */
    public function listDNS()
    {
        return $this->doCall("v2/domains", 'GET', false, $this->apiKeyHeader());
    }

    public function getDNSData(string $domain)
    {
        return $this->doCall("v2/domains/$domain", 'GET', false, $this->apiKeyHeader());
    }

    public function dnsCreateDomain(string $domain, string $server_ip, bool $dns_sec = false)
    {
        $post = ["domain" => $domain, "serverip" => $server_ip, "dns_sec" => $dns_sec];
        return $this->doCall("v2/domains", 'POST', true, $this->apiKeyHeader(), $post);
    }

    public function dnsCreateRecord(string $domain, string $name, string $type, string $data)
    {
        $post = ["domain" => $domain, "name" => $name, "type" => $type, "data" => $data];
        return $this->doCall("v2/domains/$domain/record", 'POST', true, $this->apiKeyHeader(), $post);
    }

    public function dnsDeleteDomain(string $domain)
    {
        return $this->doCall("v2/domains", 'DELETE', true, $this->apiKeyHeader(), ["domain" => $domain]);
    }

    public function dnsDeleteRecord(string $domain, string $record_id)
    {
        return $this->doCall("v2/domains/$domain/record/$record_id", 'DELETE', true, $this->apiKeyHeader());
    }

    public function dnsEnableDNSSEC(string $domain, string $status = 'enable')
    {
        return $this->doCall("v2/domains/$domain", 'PUT', true, $this->apiKeyHeader(), ["dns_sec" => $status]);
    }

    public function dnsUpdateSOA(string $domain, string $nsprimary, string $email)
    {
        $post = ["nsprimary" => $nsprimary, "email" => $email];
        return $this->doCall("v2/domains/$domain/soa", 'PATCH', true, $this->apiKeyHeader(), $post);
    }

    public function dnsUpdateRecord(string $domain, string $record_id, string $name, string $data)
    {
        $post = ["name" => $name, "data" => $data];
        return $this->doCall("v2/domains/$domain/record/$record_id", 'PATCH', true, $this->apiKeyHeader(), $post);
    }

    public function dnsSOAINFO($domain)
    {
        return $this->doCall("v2/domains/$domain/soa", 'GET', false, $this->apiKeyHeader());
    }

    public function dnsListRecordsDomain($domain)
    {
        return $this->doCall("v2/domains/$domain/records", 'GET', false, $this->apiKeyHeader());
    }

    public function dnsDNSSECInfo($domain)
    {
        return $this->doCall("v2/domains/$domain/dnssec", 'GET', false, $this->apiKeyHeader());
    }

    /*
     * PLANS
     */
    public function listPlans(string $type = 'all')// all|vc2|ssd|vdc2|dedicated|vc2z
    {
        return $this->doCall("v2/plans?type=$type", 'GET', false);
    }

    public function listBareMetalPlans()
    {
        return $this->doCall("v2/plans-metal", 'GET', false);
    }

    /*
     * REGIONS
     */
    public function listRegions()// List regions that only have plans available
    {
        return $this->doCall("v2/regions", 'GET', false);
    }

    public function regionAvailability(string $region_id, string $type = 'all')// all|vc2|ssd|vdc2|dedicated|vc2z
    {
        return $this->doCall("v2/regions/$region_id/availability", 'GET', false, [], ["type" => $type]);
    }

    /*
     * OPERATING SYSTEMS
     */
    public function listOS()
    {
        return $this->doCall("v2/os", 'GET', false);
    }

    public function osName(int $os_id): string
    {
        $data = json_decode($this->listOS(), true);
        foreach ($data['os'] as $os) {
            if ($os['id'] === $os_id) {
                return $os['name'];
            }
        }
        return "None found for OS id $os_id";
    }

    /*
     * APPLICATIONS
     */
    public function listApps()
    {
        return $this->doCall("v2/applications", 'GET', false);
    }

    /*
     * USER MANAGEMENT
     */
    public function getUsers()
    {
        return $this->doCall("v2/users", 'GET', false, $this->apiKeyHeader());
    }

    public function listUser(string $user_id)
    {
        return $this->doCall("v2/users/$user_id", 'GET', false, $this->apiKeyHeader());
    }

    public function createUser(string $email, string $name, string $password, bool $api_enabled = false, array $acls = ['subscriptions_view'])
    {
        $post = [
            "email" => $email,
            "name" => $name,
            "password" => $password,
            "api_enabled" => $api_enabled,
            "acls" => $acls];
        return $this->doCall("v2/users", 'POST', false, $this->apiKeyHeader(), $post);
    }

    public function deleteUser(string $user_id)
    {
        return $this->doCall("v2/users/$user_id ", 'DELETE', true, $this->apiKeyHeader());
    }

    public function updateUser(string $user_id, string $email, string $name, string $password, bool $api_enabled = false, array $acls = ['subscriptions_view'])
    {
        $post = [
            "email" => $email,
            "name" => $name,
            "password" => $password,
            "api_enabled" => $api_enabled,
            "acls" => $acls];
        return $this->doCall("v2/users/$user_id", 'PATCH', true, $this->apiKeyHeader(), $post);
    }

    /*
     * OBJECT STORAGE
     */
    public function listObjectStorage()
    {
        return $this->doCall("v2/object-storage", 'GET', false, $this->apiKeyHeader());
    }

    public function getObjectStorageData(string $obj_id)
    {
        return $this->doCall("v2/object-storage/$obj_id", 'GET', false, $this->apiKeyHeader());
    }

    public function listObjectStorageCluster()
    {
        return $this->doCall("v2/object-storage/clusters", 'GET', false);
    }

    public function createObjectStorage(int $cluster_id, string $label)
    {
        $post = ["cluster_id" => $cluster_id, "label" => $label];
        return $this->doCall("v2/object-storage", 'POST', false, $this->apiKeyHeader(), $post);
    }

    public function deleteObjectStorage(string $obj_id)
    {
        return $this->doCall("v2/object-storage/$obj_id", 'DELETE', true, $this->apiKeyHeader());
    }

    public function labelObjectStorage(string $label, string $obj_id)
    {
        return $this->doCall("v2/object-storage/$obj_id", 'PUT', true, $this->apiKeyHeader(), ["label" => $label]);
    }

    public function s3keyRegenObjectStorage(string $obj_id)
    {
        $post = ["object-storage-id" => $obj_id];
        return $this->doCall("v2/object-storage/$obj_id/regenerate-keys", 'POST', false, $this->apiKeyHeader(), $post);
    }

    /*
     * HELPER FUNCTIONS
    */
    public function convertBytes(int $bytes, string $convert_to = 'GB', bool $format = true, int $decimals = 2): float|int
    {
        if ($convert_to === 'GB') {
            $value = ($bytes / 1073741824);
        } elseif ($convert_to === 'MB') {
            $value = ($bytes / 1048576);
        } elseif ($convert_to === 'KB') {
            $value = ($bytes / 1024);
        } else {
            $value = $bytes;
        }
        if ($format) {
            return (float)number_format($value, $decimals);
        }

        return $value;
    }

    public function boolToInt(bool $bool): int
    {
        return ($bool) ? 1 : 0;
    }

}