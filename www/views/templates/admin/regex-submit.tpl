
<h1>{$page->title}</h1>

<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nam libero eros, aliquet a vehicula sed, feugiat a nunc. Vestibulum in dictum leo. Donec quis tortor dui, aliquet euismod libero. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Aenean ut nisi eget odio tincidunt vestibulum. <strong>Thank you!</strong></p>

{ if $upload_status eq 'OK' }
<div style="background-color: #CDEB8B; color: #fff; padding: 20px">
  <strong>Your regex file was uploaded. Thank you for contributing.</strong>
</div>
<br />
{ /if }

{ if $upload_status eq 'BAD' }
<div style="background-color: #CDEB8B; color: #CC0000; padding: 20px">
  <strong>Failed to upload your regex file :-( - please try again.</strong>
</div>
<br />
{ /if }

<form action="{$SCRIPT_NAME}" method="post" name="submit_regex">
  <input type="hidden" name="regex_submit_please" value="1" />
  <input type="submit" name="submit" value="Submit regular expressions" />
</form>

<br />

<p>
  <strong>Filename being submitted: </strong> {$regex_filename}
  <br />
  <strong>Contents</strong>
  <br />
  <pre>
  {$regex_contents|print_r}
  </pre>
</p>

