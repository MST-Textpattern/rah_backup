<?php

/**
 * Transfers backups made by rah_backup to offsite location via FTP.
 * 
 * @package rah_backup
 * @author Jukka Svahn <http://rahforum.biz>
 * @copyright (c) 2011 Jukka Svahn
 * @license GLPv2
 *
 * This rah_backup module requires PHP's FTP extension.
 * <http://www.php.net/manual/en/book.ftp.php>
 */

/**
 * @global $rah_backup__module_ftp_offsite array
 */

	global $rah_backup__module_ftp_offsite;

/**
 * Your configuration. Used to connect to remote server.
 * @global $host string FTP server's address (i.e. domain.ltd or IP).
 * @global $port int Remote server's FTP port
 * @global $user string Remote server's username.
 * @global $pass string Remote server's password.
 * @global $path string Path to directory used to store the backups on the remote server.
 * @global $passive bool Turns passive mode on or off.
 * @global $as_binary bool Transfer files in binary mode. If FALSE, ASCII mode is used instead.
 */

	$rah_backup__module_ftp_offsite[] = array(
		'host' => '',
		'port' => 21,
		'user' => '',
		'pass' => '',
		'path' => '/path/to/remote/directory/',
		'passive' => true,
		'as_binary' => true,
	);

/**
 * Registers the function. Hook to event 'rah_backup_tasks', step 'backup_done'.
 */

	register_callback('rah_backup__module_ftp_offsite','rah_backup_tasks','backup_done');

/**
 * Sends new backup files to off site
 * @param $event string Callback event.
 * @param $step string Callback step.
 * @param $data mixed Data passed to the callback function.
 * @return bool FALSE when FTP extension isn't available, TRUE otherwise even when uploading failed.
 */

	function rah_backup__module_ftp_offsite($event, $step, $data) {
		
		global $rah_backup__module_ftp_offsite;
		
		if(!function_exists('ftp_connect') || is_disabled('ftp_connect'))
			return false;
		
		foreach((array) $rah_backup__module_ftp_offsite as $cfg) {
		
			if(!$cfg['host'] || (($ftp = ftp_connect($cfg['host'], $cfg['port'])) && !$ftp))
				continue;
			
			if(@ftp_login($ftp, $cfg['user'], $cfg['pass'])) {
				ftp_pasv($ftp, (bool) $cfg['passive']);
				
				if(!$cfg['path'] || @ftp_chdir($ftp, $cfg['path'])) {
					foreach($data['files'] as $file) {
						@ftp_put($ftp, basename($file), $file, ($cfg['as_binary'] ? FTP_BINARY : FTP_ASCII));
					}
				}

			}
		
			ftp_close($ftp);
		}
		
		return true;
	}
?>