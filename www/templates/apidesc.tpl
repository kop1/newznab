
			<h1>{$page->title}</h1>

			<p>
				Here lives the documentation for the api for accessing nzb and index data. Api functions can be called by either logged in 
				users, or using the site api key.
			</p>
			
			<h2>Available Functions</h2>
			<p>
				Use the parameter <span style="font-family:courier;">?t=</span> to specify the function being called 
				and <span style="font-family:courier;">?k=</span> to provide the site api key. Please <a href="{$smarty.const.WWW_TOP}/contact-us.php">contact us</a> for details of the site api key.
				<ul>
					<li>
						Search <span style="font-family:courier;"><a href="{$smarty.const.WWW_TOP}/api?t=s&q=linux">?t=s&q=linux</a></span>
						<br/>
						Returns a list of nzbs matching a query.
						<br/>
						or for a TV Rage ID <span style="font-family:courier;"><a href="{$smarty.const.WWW_TOP}/api?t=s&rid=20423&season=s01">?t=s&rid=20423&season=s01</a></span>. Either numeric (1) or string (E01) can be used for searching by series or episode.
					</li>
					<li>
						Individual <span style="font-family:courier;"><a href="{$smarty.const.WWW_TOP}/api?t=i&id=9ca52909ba9b9e5e6758d815fef4ecda">?t=i&id=9ca52909ba9b9e5e6758d815fef4ecda</a></span>
						<br/>
						Returns information about an nzb.
						<br/>
						or for a TV Rage ID <span style="font-family:courier;"><a href="{$smarty.const.WWW_TOP}/api?t=i&rid=20423&season=s01&ep=e02">?t=i&rid=20423&season=s01&ep=e02</a></span>
					</li>						
					<li>
						Get <span style="font-family:courier;"><a href="{$smarty.const.WWW_TOP}/api?t=g&id=9ca52909ba9b9e5e6758d815fef4ecda">?t=g&id=9ca52909ba9b9e5e6758d815fef4ecda</a></span>
						<br/>
						Downloads the nzb file associated with an Id.
						<br/>
						or for a TV Rage ID <span style="font-family:courier;"><a href="{$smarty.const.WWW_TOP}/api?t=g&rid=20423&season=s01&ep=e02">?t=g&rid=20423&season=s01&ep=e02</a></span>
					</li>	
					<li>
						Capabilities <span style="font-family:courier;"><a href="{$smarty.const.WWW_TOP}/api?t=c">?t=c</a></span>
						<br/>
						Reports the capabilities if the server. Includes information about the server name, available search categories and version number of the software.
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
						Xml (default) <span style="font-family:courier;">?t=s&q=linux&o=xml</span>
						<br/>
						Returns the data in an xml document.
					</li>
					<li>
						Json <span style="font-family:courier;">?t=s&q=linux&o=json</span>
						<br/>
						Returns the data in a json object.
					</li>						
				</ul>
			</p>
			

			