
			<h1>{$page->title}</h1>

			<p>
				Here you can choose rss feeds from site categories. The feeds will present either decriptions or 
				downloads of Nzb files.
			</p>
			
			<p>
				<ul>
					<li>
						Add this string to your feed URL to allow NZB downloads without logging in: <span style="font-family:courier;">&i={$userdata.ID}&r={$userdata.rsstoken}</span>
					</li>
					<li>
						To change the default link to download an nzb: <span style="font-family:courier;">&dl=1</span>
					</li>
					<li>
						To change the number of results (default is 25, max is 100) returned: <span style="font-family:courier;">&num=50</span> 
					</li>
				</ul>
			</p>
			
			<p>
				Most Nzb clients which support Nzb rss feeds will appreciate the full URL, with download link and your user token.
			</p>
			
			<p>
				The feeds include additional attributes to help provide better filtering in your Nzb client, such as size, group and categorisation.
			</p>
			
			<h2>Available Feeds</h2>
			<p>
				<ul style="text-align: left;">
					<li>
						Full site feed<br/>
						<a href="{$smarty.const.WWW_TOP}/rss?t=0&dl=1&i={$userdata.ID}&r={$userdata.rsstoken}">{$smarty.const.WWW_TOP}/rss/?t=0&dl=1&i={$userdata.ID}&r={$userdata.rsstoken}</a>
					</li>
					<li>
						My cart feed<br/>
						<a href="{$smarty.const.WWW_TOP}/rss?t=-2&dl=1&i={$userdata.ID}&r={$userdata.rsstoken}">{$smarty.const.WWW_TOP}/rss/?t=-2&dl=1&i={$userdata.ID}&r={$userdata.rsstoken}</a>
					</li>
					{foreach from=$categorylist item=category}
						<li>
							{$category.title} feed <br/>
							<a href="{$smarty.const.WWW_TOP}/rss?t={$category.ID}&dl=1&i={$userdata.ID}&r={$userdata.rsstoken}">{$smarty.const.WWW_TOP}/rss/?t={$category.ID}&dl=1&i={$userdata.ID}&r={$userdata.rsstoken}</a>
						</li>
					{/foreach}
				</ul>
			</p>
			
