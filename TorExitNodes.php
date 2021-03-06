<?php

/**
 * Classe que disponibiliza um mecanismo de identificação de IPs
 * que tem como origem a rede TOR.
 */
class TorExitNodes
{
    protected $exit_addresses_url = 'https://check.torproject.org/exit-addresses';
    protected $exit_addresses_file = 'exit_addresses.txt';
    protected $update_interval = 4; // hours

	/**
	 * Método para setar intervalo do cache
	 * 
	 * @param $interval INTEGER - Numero de horas
	 */
	public function setUpdateInterval($interval)
	{
		$this->update_interval = $interval;
	}
	
	/**
	 * Método para setar o caminho do cache
	 * 
	 * @param $path TEXT - Caminho para o arquivo do cache
	 */
	public function setAddressesFile($path)
	{
		$this->exit_addresses_file = $path;
	}
	
    /**
     * Método que faz o download de todas as informações
     * a respeito dos exit nodes.
     *
     * @return string
     */
    public function loadExitAddresses()
    {
        return file_get_contents($this->exit_addresses_url);
    }

    /**
     * Método que manipula o texto bruto disponível na página dos exit addresses
     * e extrai os IPs, depois armazena tudo em um arquivo TXT.
     *
     * @return void
     */
    public function updateExitAddressIps()
    {
        $exit_addresses = $this->loadExitAddresses();

        $pattern = '/([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})/';

        if(preg_match_all($pattern, $exit_addresses, $ips)) {
            file_put_contents($this->exit_addresses_file, implode("\n", $ips[0]));
        }
    }

    /**
     * Método responsável por verificar o tempo da última atualização dos IPs,
     * caso a última atualização for a mais de 4 horas, executa a
     * atualização dos IPs novamente.
     *
     * @return void
     */
    public function checkFileModifiedTime()
    {
        $file_modified_time = filemtime($this->exit_addresses_file);
		
		// Verifica se foi possivel ler o arquivo
        if(!$file_modified_time) {
    		$this->updateExitAddressIps();
    		return TRUE;
        }
        
        $date_mfile = new Datetime(date('Y-m-d H:i:s', $file_modified_time));
        $date_now = new Datetime('now');

        // se o arquivo foi modificado a mais de 4 horas, executa o update.
        if($date_mfile->diff($date_now)->h >= $this->update_interval) {
            $this->updateExitAddressIps();
        }
    }

    /**
     * Método que verifica se um determinado IP é um exit node da rede TOR.
     *
     * @param string $ip
     * @return bool
     */
    public function isTorExitNode($ip)
    {
        $this->checkFileModifiedTime();
        $ips = file_get_contents($this->exit_addresses_file);

        if(in_array($ip, explode("\n", $ips))) {
            return true;
        }
        return false;
    }
    
	/**
	 * Método experimental para verificação rapida sem consumo de memoria
	 * 
	 * Verificar tambem a possibilidade de usar o comando exec, para assim utilizar o comando GREP
	 */
	public function _fastTorVerify($ip)
	{
		$this->checkFileModifiedTime();
		
		$handle = fopen($this->exit_addresses_file, "r");
		if($handle) {
			while (($buffer = fgets($handle)) !== FALSE) {
				if (strpos($buffer, $ip) !== FALSE) {
					return TRUE;
					break;
				}
			}
			fclose($handle);
			
			return FALSE;
		}
	}
}

?>
