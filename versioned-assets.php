<?php
/** \versioned_assets.php
  * \Copyright (c) 2017 Said Achmiz
  * \Licensed under the MIT License
  * \brief Adds versions (modification timestamps) to attachment URLs, so that browser
  *  caches invalidate properly when attachments are updated.
  */
$RecipeInfo['VersionedAssets']['Version'] = '2017-12-13';

global $LinkFunctions;
$LinkFunctions['Attach:'] = 'LinkUploadVersioned';

SDV($VersionedAssetsReattachFileExtension, true);

function LinkUploadVersioned($pagename, $imap, $path, $alt, $txt, $fmt=NULL) {
	global $FmtV, $UploadFileFmt, $LinkUploadCreateFmt,
		$UploadUrlFmt, $UploadPrefixFmt, $EnableDirectDownload;
	if (preg_match('!^(.*)/([^/]+)$!', $path, $match)) {
		$pagename = MakePageName($pagename, $match[1]);
		$path = $match[2];
	}
	$upname = MakeUploadName($pagename, $path);
	$encname = rawurlencode($upname);
	$filepath = FmtPageName("$UploadFileFmt/$upname", $pagename);
	$FmtV['$LinkUpload'] =
		FmtPageName("\$PageUrl?action=upload&amp;upname=$encname", $pagename);
	$FmtV['$LinkText'] = $txt;
	if (!file_exists($filepath)) 
		return FmtPageName($LinkUploadCreateFmt, $pagename);
	$path = PUE(FmtPageName(IsEnabled($EnableDirectDownload, 1) 
								? "$UploadUrlFmt$UploadPrefixFmt/$encname"
								: "{\$PageUrl}?action=download&amp;upname=$encname",
							$pagename));
	
	## Append the modification time to the URL as a GET parameter; this should be ignored
	## by the web server, but is seen as part of the unique URL of the remote resource by
	## the browser; when it changes (because the attachment has been modified), the 
	## browser will see that it doesn’t have a cached version of the resource under the
	## new URL, and will retrieve the updated version.
	$versioned_path = $path . "?v=" . filemtime($filepath);
	
	global $VersionedAssetsReattachFileExtension;
	if ($VersionedAssetsReattachFileExtension == true) {
		## Re-attach the file extension, so that LinkIcons and such things work properly.
		preg_match("/\\.[^\\.]+$/", $path, $matches);
		$versioned_path .= $matches[0];
	}
	
	return LinkIMap($pagename, $imap, $versioned_path, $alt, $txt, $fmt);
}

