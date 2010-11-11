<?php

require_once("config.php");
require_once(WWW_DIR."/lib/adminpage.php");
require_once(WWW_DIR."/lib/releaseregex.php");

$page           = new AdminPage();
$page->title    = "Submit your regex expressions to the Official Database";

// Logic
$regex          = new ReleaseRegex();
$regexList      = $regex->get(false, -1, true, true);
$regexSerialize = serialize($regexList);
$regexFilename  = 'releaseregex-' . time() . '.regex';

// User wants to submit their regex's
if (isset($_POST['regex_submit_please']))
{
  // Delete old regex files
  $oldFiles = glob(WWW_DIR . '/temp/*.regex') ? glob(WWW_DIR . '/temp/*.regex') : array();
  foreach ($oldFiles as $oldRegexFilename) @unlink($oldRegexFilename);

  // Create new regex file
  if (file_put_contents(WWW_DIR . "/temp/$regexFilename", $regexSerialize))
  {
    // Continue processing
    if (file_exists(WWW_DIR . "/temp/$regexFilename") &&
        is_readable(WWW_DIR . "/temp/$regexFilename"))
    {
      // Submit

      $ch = curl_init();
      curl_setopt($ch, CURLOPT_HEADER, 0);
      curl_setopt($ch, CURLOPT_VERBOSE, 0);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
      curl_setopt($ch, CURLOPT_URL,"http://newznab.com/regex/upload.php");
      curl_setopt($ch, CURLOPT_POST, true);
      $post = array(
          "regex_file" => "@" . WWW_DIR . "/temp/$regexFilename",
      );
      curl_setopt($ch, CURLOPT_POSTFIELDS, $post); 
      $response = curl_exec($ch);

      echo curl_error($ch);

      curl_close($ch);

      if ($response == 'OK')
      {
        $page->smarty->assign('upload_status', 'OK');

        // Remove old regex file
        @unlink(WWW_DIR . "/temp/$regexFilename");
      }
      else
      {
        $page->smarty->assign('upload_status', 'BAD');
      }
    }
    else
    {
      $regexFilename = 'Unable to generate file! Please try again.';
    }
  }
}

// Assigns
$page->smarty->assign('regex_filename', $regexFilename);
$page->smarty->assign('regex_contents', $regexList);

// Render
$page->content  = $page->smarty->fetch('regex-submit.tpl');
$page->render();

# vim:ft=php ts=2

