<?php

class TorExitNodes
{
    private $exit_addresses_url = 'https://check.torproject.org/exit-addresses';
    private $exit_addresses_file = 'exit_addresses.txt';
    private $update_interval = 4; // hours

    public function loadExitAddresses()
    {
        $exit_addresses = file_get_contents($this->exit_addresses_url);
        return $exit_addresses;
    }

    public function updateExitAddressIps()
    {
        $exit_addresses = $this->loadExitAddresses();

        $pattern = '/([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})/';

        if(preg_match_all($pattern, $exit_addresses, $ips)) {
            file_put_contents('exit_addresses.txt', implode("\n", $ips[0]));
        }
    }

    public function checkFileModifiedTime()
    {
        $file_modified_time = filemtime($this->exit_addresses_file);

        $date_mfile = new Datetime(date('Y-m-d H:i:s', $file_modified_time));
        $date_now = new Datetime('now');

        // if the file was modified more than 4 hours run update.
        if($date_mfile->diff($date_now)->h >= $this->update_interval) {
            $this->updateExitAddressIps();
        }
    }

    public function isTorExitNode($ip)
    {
        $this->checkFileModifiedTime();
        $ips = file_get_contents($this->exit_addresses_file);

        if(in_array($ip, explode("\n", $ips))) {
            return true;
        }
        return false;
    }
}

?>
