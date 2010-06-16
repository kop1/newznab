
			<h1>{$page->title}</h1>

			<p>
				Here lives the documentation for the api for accessing nzb and index data. Api functions can be
				called by either logged in users, or by providing an apikey.
			</p>
			
			{if $loggedin=="true"}
				<p>
					Your credentials should be provided as <span style="font-family:courier;">?apikey={$userdata.rsstoken}</span>
				</p>
			{/if}
			
			<h2>Available Functions</h2>
			<p>
				Use the parameter <span style="font-family:courier;">?t=</span> to specify the function being called.
				<ul>
					<li>
						Capabilities <span style="font-family:courier;"><a href="{$smarty.const.WWW_TOP}/api?t=caps">?t=caps</a></span>
						<br/>
						Reports the capabilities if the server. Includes information about the server name, available search categories and version number of the newznab protocol being used.
						<br/>
						Capabilities does not require any credentials in order to be ran.
					</li>	
					<li>
						Search <span style="font-family:courier;"><a href="{$smarty.const.WWW_TOP}/api?t=search&q=linux">?t=search&q=linux</a></span>
						<br/>
						Returns a list of nzbs matching a query. You can also  filter by site category by including a comma separated list of categories as follows <span style="font-family:courier;">&cat=123,124</span>
						<br/>
						or for a TV Rage ID <span style="font-family:courier;"><a href="{$smarty.const.WWW_TOP}/api?t=search&rid=20423&season=s01">?t=search&rid=20423&season=s01</a></span>. Either numeric (1) or string (E01) can be used for searching by series or episode.
					</li>
					<li>
						Details <span style="font-family:courier;"><a href="{$smarty.const.WWW_TOP}/api?t=details&id=9ca52909ba9b9e5e6758d815fef4ecda">?t=details&id=9ca52909ba9b9e5e6758d815fef4ecda</a></span>
						<br/>
						Returns detailed information about an nzb.
					</li>						
					<li>
						Get <span style="font-family:courier;"><a href="{$smarty.const.WWW_TOP}/api?t=get&id=9ca52909ba9b9e5e6758d815fef4ecda">?t=get&id=9ca52909ba9b9e5e6758d815fef4ecda</a></span>
						<br/>
						Downloads the nzb file associated with an Id.
						<br/>
						or for a TV Rage ID <span style="font-family:courier;"><a href="{$smarty.const.WWW_TOP}/api?t=get&rid=20423&season=s01&ep=e02">?t=get&rid=20423&season=s01&ep=e02</a></span>
					</li>	
				</ul>
			</p>

			<h2>Output Format</h2>
			<p>
				Obviously not appropriate to functions which return an nzb file.
			</p>
			<p>
				<ul>
					<li>
						Xml (default) <span style="font-family:courier;">?t=search&q=linux&o=xml</span>
						<br/>
						Returns the data in an xml document.
					</li>
					<li>
						Json <span style="font-family:courier;">?t=search&q=linux&o=json</span>
						<br/>
						Returns the data in a json object.
					</li>						
				</ul>
			</p>
			

			