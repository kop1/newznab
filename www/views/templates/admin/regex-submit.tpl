
<h1>{$page->title}</h1>

<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nam libero eros, aliquet a vehicula sed, feugiat a nunc. Vestibulum in dictum leo. Donec quis tortor dui, aliquet euismod libero. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Aenean ut nisi eget odio tincidunt vestibulum. <strong>Thank you!</strong></p>

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

