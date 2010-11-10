
<h1>{$page->title}</h1>

<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nam libero eros, aliquet a vehicula sed, feugiat a nunc. Vestibulum in dictum leo. Donec quis tortor dui, aliquet euismod libero. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Aenean ut nisi eget odio tincidunt vestibulum. <strong>Thank you!</strong></p>

<form action="." method="post" id="submit_regex">

  <input type="submit" name="submit" value="Send regular expressions" />

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

