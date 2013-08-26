<%header%>
					<a name="message"></a>
					<h2>N2F Power!</h2>
					<p>
						Congratulations on installing N2 Framework - Yverdon successfully!  Be sure to check out the
						<strong><a href="./?nmod=wheretostart">Where To Start</a></strong> section, as well as the
						<strong><a href="./?nmod=resources">Resources</a></strong> section to get the help you need
						turning this simple framework into a fast and powerful web site or application.  Thanks for
						trying us out and we hope you enjoy!<br />
						<br />
						<strong>- The N2F Staff</strong>
					</p>

					<a name="debugdump"></a>
					<h2>Sample Debug Information</h2>
					<div class="main-content">
						Click <strong><a href="javascript: //;" onclick="toggleDiv('debugdiv');">here</a></strong> to see some sample debug information from the N2F Yverdon system.
						<div id="debugdiv" style="padding: 10px; overflow: scroll; height: 150px; display: none">
							<%$debug_information%>
						</div>
					</div>

					<br />

					<a name="dumpexts"></a>
					<h2>Loaded Extensions</h2>
					<div class="main-content">
						Click <strong><a href="javascript: //;" onclick="toggleDiv('extsdiv');">here</a></strong> to see the currently loaded extensions.
						<div id="extsdiv" style="padding: 10px; overflow: scroll; height: 150px; width: 485px; display: none">
<% foreach (array_values($registered_exts) as $ext): %>							<div class="extension">
								<span class="name"><%$ext['name']%></span> v<%$ext['version']%><br />
								By: <%$ext['author']%><br />
								<a href="<%$ext['url']%>" target="_blank"><%$ext['url']%></a>
								<hr />
							</div>
<% endforeach; %>
						</div>
					</div>

					<br />
<%footer%>