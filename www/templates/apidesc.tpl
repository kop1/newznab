
			<h1>{$page->title}</h1>

			<p>
				Here lives the documentation for the api for accessing nzb data within the index. Api functions can be called by either logged in 
				users, or using the site api key.
			</p>
			
			<h2>Available Functions</h2>
			<p>
				Use the parameter <span style="font-family:courier;">?t=</span> to specify the function being called.
				<ul>
					<li>
						Search <span style="font-family:courier;">?t=s&q=linux</span>
						<br/>
						Returns an list of nzbs matching a query.
					</li>
					<li>
						Individual <span style="font-family:courier;">?t=i&id=9ca52909ba9b9e5e6758d815fef4ecda</span>
						<br/>
						Returns information about an nzb.
					</li>						
					<li>
						Get <span style="font-family:courier;">?t=g&id=9ca52909ba9b9e5e6758d815fef4ecda</span>
						<br/>
						Downloads the nzb file associated with an Id.
					</li>	
				</ul>
			</p>

			<h2>Output Format</h2>
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
			

			