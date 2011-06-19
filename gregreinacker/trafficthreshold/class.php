<?php
/******************************************************************************
 Pepper
 
 Developer		: Greg Reinacker
 Plug-in Name	: Traffic Threshold
 
 http://www.rassoc.com/
 
 Note that this pepper uses unix shared memory segments; it will allocate a
 shared 256 byte segment, and also create a semaphore to synchronize access.
 You can see these by running 'ipcs' from the terminal, and you can remove
 them if you wish using 'ipcrm'. These shared resources will be removed if
 you click 'Uninstall' from the Mint preferences.

 ******************************************************************************/
 
$installPepper = "GR_TrafficThreshold";
	
class GR_TrafficThreshold extends Pepper
{
	var $version	= 100; 
	var $info		= array
	(
		'pepperName'	=> 'Traffic Threshold',
		'pepperUrl'		=> 'http://www.rassoc.com/gregr/weblog/2011/04/26/traffic-threshold-pepper-extension-for-mint-stats/',
		'pepperDesc'	=> 'The Traffic Threshold pepper will send you an alert via email when a certain number of page views per minute has been exceeded.',
		'developerName'	=> 'Greg Reinacker',
		'developerUrl'	=> 'http://www.rassoc.com/gregr/weblog/'
	);
	var $prefs = array
	(
		'thresholdViewsPerMin' => '10',
		'alertEmailTo' => '',
		'alertEmailFrom' => ''
	);
	var $manifest = array
	(
	);

	/**************************************************************************
	 isCompatible()
	 **************************************************************************/
	function isCompatible()
	{
		if ($this->Mint->version >= 219)
		{
			return array
			(
				'isCompatible'	=> true
			);
		}
		else
		{
			return array
			(
				'isCompatible'	=> false,
				'explanation'	=> '<p>This Pepper is only compatible with Mint 2.19 and higher.</p>'
		);
		}
	}
	
	/**************************************************************************
	 onJavaScript()
	 **************************************************************************/
	function onJavaScript() 
	{
	}
	
	/**************************************************************************
	 onInstall()
	 Check to see if shared memory is available on this installation
	 **************************************************************************/
	function onInstall()
	{
	}
	
	/**************************************************************************
	 onRecord()
	 **************************************************************************/
	function onRecord() 
	{
		if (empty($this->prefs['alertEmailTo']) || empty($this->prefs['alertEmailFrom']))
			return array();
			
		$threshold = $this->prefs['thresholdViewsPerMin'];
		
		$size = 256;
		$key = ftok(__FILE__,'A');
		
		$shmid = shm_attach($key,$size,0666);
		if (empty($shmid))
			return array();	
	
		$semid = sem_get($key,1,0666,true);
		if (empty($shmid))
			return array();	

		$ok = sem_acquire($semid);
		if ($ok)
		{
			$res = shm_get_var($shmid,1);
			if ($res == FALSE)
				$res = array();
				
			if (empty($res[0]))
			{
				$res[0] = array(0,0);
				$res[0][0] = time();
			}
			
			$timestamp = $res[0][0];
			$now = time();
			$minute = date('i',$timestamp);
			$newmin = date('i', $now);
			
			if ($newmin != $minute)
			{
				// we have started a new minute, reset counter
				$res[0][0] = time();
				$res[0][1] = 0;
			}

			if (++$res[0][1] == $threshold)
			{
				// alert
				$datestr = date(DATE_RFC1123,$now);
				
				$message = "Alert: Page views exceeded 1-minute threshold (" . $threshold . ") at " . $datestr;
				
				$message = wordwrap($message,70);
				mail($this->prefs['alertEmailTo'], 'Page view alert', $message, 'From: ' . $this->prefs['alertEmailFrom']);
			}
			
			$ok = shm_put_var($shmid,1,$res);
			$ok = sem_release($semid);
			$ok = shm_detach($shmid);
		}
		
		return array();
	}
	
	
	/**************************************************************************
	 onDisplayPreferences()
	 **************************************************************************/
	function onDisplayPreferences() 
	{
		$ok = $this->testNecessaryFunctionality();

		if (!$ok)
		{
		$preferences['Traffic Threshold']	= <<<HERE
<table>
	<tr>
	<tr>
		<td></td>
		<td style="color:yellow;">Unfortunately, it appears your system is not compatible with the Traffic Threshold pepper. Specifically, it seems that the shared memory and/or synchronization functions required are not available in PHP on this system.<br/><br/>Click the Uninstall button to remove this pepper.</td>
	</tr>
</table>
HERE;
		}
		else
		{


		$preferences['Traffic Threshold']	= <<<HERE
<table>
	<tr>
		<th scope="row">Threshold</th>
		<td><span><input type="text" id="trafficThreshold" name="trafficThreshold" value="{$this->prefs['thresholdViewsPerMin']}" /></span></td>
	</tr>
	<tr>
		<td></td>
		<td>(in page views per minute)</td>
	</tr>
</table>
HERE;

		$preferences['Alert Emails']	= <<<HERE
<table>
	<tr>
		<th scope="row">Send alerts to:</th>
		<td><span><input type="text" id="emailTo" name="emailTo" value="{$this->prefs['alertEmailTo']}" /></span></td>
	</tr>
	<tr>
		<th scope="row">Send from:</th>
		<td><span><input type="text" id="emailFrom" name="emailFrom" value="{$this->prefs['alertEmailFrom']}" /></span></td>
	</tr>
	<tr>
		<td></td>
		<td>(both addresses are required)</td>
	</tr>
</table>
	
HERE;
		
		}
		return $preferences;
	}
	
	/**************************************************************************
	 onSavePreferences()
	 **************************************************************************/
	function onSavePreferences() 
	{
		$this->prefs['thresholdViewsPerMin'] = $this->escapeSQL($_POST['trafficThreshold']);
		$this->prefs['alertEmailTo'] = $this->escapeSQL($_POST['emailTo']);
		$this->prefs['alertEmailFrom'] = $this->escapeSQL($_POST['emailFrom']);
	}

	/**************************************************************************
	 onUninstall()
	 **************************************************************************/
	function onUninstall()
	{
		// it's possible we got here but we're on an unsupported system
		
		if (!function_exists('shm_attach') || !function_exists('sem_get'))
			return;

		// delete the shared resources we've been using here
		
		$size = 256;
		$key = ftok(__FILE__,'A');
		$shmid = shm_attach($key,$size,0666);
		if (!empty($shmid))
		{
			shm_remove($shmid);
		}

		$semid = sem_get($key,1,0666,true);
		if (!empty($semid))
		{
			sem_remove($semid);
		}
	}
	
	/**************************************************************************
	 testNecessaryFunctionality()
	 
	 Tests to make sure shared memory and synchronization functions are available
	 **************************************************************************/
	function testNecessaryFunctionality()
	{
		if (!function_exists('shm_attach') || !function_exists('sem_get'))
			return false;

		$size = 256;
		$key = ftok(__FILE__,'A');
		$shmid = shm_attach($key,$size,0666);
		if (empty($shmid))
			return false;
		shm_detach($shmid);
			
		$semid = sem_get($key,1,0666,true);
		if (empty($semid))
			return false;
		
		return true;
	}
}
