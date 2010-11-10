<?php

require_once("config.php");
require_once(WWW_DIR."/lib/adminpage.php");
require_once(WWW_DIR."/lib/releaseregex.php");

$page           = new AdminPage();
$page->title    = "Submit your regex expressions to the Official Database";

// Logic
$regex          = new ReleaseRegex();
$regexList      = $regex->get(false, -1, true);
$regexSerialize = serialize($regexList);
$regexFilename  = 'releaseregex-' . time() . '.regex';

// User wants to submit their regex's
if (isset($_POST['regex_submit_please']))
{
  // Delete old regex files
  foreach (glob(WWW_DIR . "/temp/*.regex") as $oldRegexFilename)
  {
    @unlink($oldRegexFilename);
  }

  // Create new regex file
  file_put_contents(WWW_DIR . "/temp/$regexFilename", $regexSerialize);

  // Continue processing
  if (file_exists(WWW_DIR . "/temp/$regexFilename") && is_readable(WWW_DIR . "/temp/$regexFilename"))
  {
    // Submit
  }
  else
  {
    $regexFilename = 'Unable to generate file! Please try again.';
  }
}

// Assigns
$page->smarty->assign('regex_filename', $regexFilename);
$page->smarty->assign('regex_contents', $regexList);

// Render
$page->content  = $page->smarty->fetch('regex-submit.tpl');
$page->render();

# vim:ft=php ts=2

