<div id="$id" class="RequestHandler FormField DataObjectManager $NestedType field" href="$CurrentLink">
	<div class="ajax-loader"></div>
	<div class="dataobjectmanager-actions <% if HasFilter %>filter<% end_if %>">
		<% if Can(add) %>
			<a class="popup-button" rel="$PopupWidth" href="$AddLink" alt="add">
				<span class="uploadlink"><img src="dataobject_manager/images/add.png" alt="" /><% sprintf(_t('DataObjectManager.ADDITEM','Add %s',PR_MEDIUM,'Add [name]'),$AddTitle) %></span>
			</a>	
		<% else %>
		  <h3>$PluralTitle</h3>
		<% end_if %>
	</div>
	<% if HasFilter %>
		<div class="dataobjectmanager-filter">
			$FilterDropdown
		</div>
	<% end_if %>
	<div style="clear:both;"></div>
	<div class="top-controls">
		<div class="rounded_table_top_right">
			<div class="rounded_table_top_left">
				<div class="Pagination">
					<% if FirstLink %><a class="First" href="$FirstLink" title="<% _t('DataObjectManager.VIEWFIRST', 'View first') %> $PageSize"><img src="dataobject_manager/images/resultset_first.png" alt="" /></a>
					<% else %><span class="First"><img  src="dataobject_manager/images/resultset_first_disabled.png" alt="" /></span><% end_if %>
					<% if PrevLink %><a class="Prev" href="$PrevLink" title="<% _t('DataObjectManager.VIEWPREVIOUS', 'View previous') %> $PageSize"><img src="dataobject_manager/images/resultset_previous.png" alt="" /></a>
					<% else %><img class="Prev" src="dataobject_manager/images/resultset_previous_disabled.png" alt="" /><% end_if %>
					<span class="Count">
						<% _t('DataObjectManager.DISPLAYING', 'Displaying') %> $FirstItem <% _t('DataObjectManager.TO', 'to') %> $LastItem <% _t('DataObjectManager.OF', 'of') %> $TotalCount
					</span>
					<% if NextLink %><a class="Next" href="$NextLink" title="<% _t('DataObjectManager.VIEWNEXT', 'View next') %> $PageSize"><img src="dataobject_manager/images/resultset_next.png" alt="" /></a>
					<% else %><img class="Next" src="dataobject_manager/images/resultset_next_disabled.png" alt="" /><% end_if %>
					<% if LastLink %><a class="Last" href="$LastLink" title="<% _t('DataObjectManager.VIEWLAST', 'View last') %> $PageSize"><img src="dataobject_manager/images/resultset_last.png" alt="" /></a>
					<% else %><span class="Last"><img src="dataobject_manager/images/resultset_last_disabled.png" alt="" /></span><% end_if %>
				</div>
				<div class="dataobjectmanager-search">
					<span class="sbox_l"></span><span class="sbox"><input value="<% if SearchValue %>$SearchValue<% else %><% _t('DataObjectManager.SEARCH','Search') %><% end_if %>" type="text" id="srch_fld"  /></span><span class="sbox_r" id="srch_clear"></span>
				</div>
				<div style="clear:both;"></div>
			</div>
		</div>
	</div>
	<div class="list column{$Headings.Count}" class="list-holder" style="width:100%;">
		<div class="dataobject-list">		
		<ul <% if ShowAll %>class="sortable-{$sourceClass}"<% end_if %>>
				<li class="head">
					<div class="fields-wrap">
					<% control Headings %>
					<div class="col $FirstLast" {$ColumnWidthCSS}>
						<div class="pad">
								<% if IsSortable %>
								<a href="$SortLink">$Title &nbsp;
								<% if IsSorted %>
									<% if SortDirection = ASC %>
									<img src="cms/images/bullet_arrow_up.png" alt="" />
									<% else %>
									<img src="cms/images/bullet_arrow_down.png" alt="" />
									<% end_if %>
								<% end_if %>
								</a>
								<% else %>
								$Title
								<% end_if %>
						</div>
					</div>
					<% end_control %>
					</div>
					<div class="actions col">&nbsp;</div>
				</li>
			<% if Items %>
			<% control Items %>
				<li class="data" id="record-$Parent.id-$ID">
						<div class="fields-wrap">
						<% control Fields %>
						<div class="col" {$ColumnWidthCSS}><div class="pad"><% if Value %>$Value<% else %>&nbsp;<% end_if %></div></div>
						<% end_control %>
						</div>
						<div class="actions col">
								<% include Actions %>
						</div>
				</li>
			<% end_control %>
			<% else %>
					<li><i><% sprintf(_t('DataObjectManager.NOITEMSFOUND','No %s found'),$PluralTitle) %></i></li>
			<% end_if %>
		</ul>
		</div>
	</div>
	<div class="bottom-controls">
		<div class="rounded_table_bottom_right">
			<div class="rounded_table_bottom_left">
				<div class="sort-control">
					<% if Sortable %>
						<input id="showall-{$id}" type="checkbox" <% if ShowAll %>checked="checked"<% end_if %> value="<% if Paginated %>$ShowAllLink<% else %>$PaginatedLink<% end_if %>" /><label for="showall-{$id}"><% _t('DataObjectManager.DRAGDROP','Allow drag &amp; drop reordering') %></label>
					<% end_if %>
				</div>
				<div class="per-page-control">
					<% if ShowAll %><% else %>$PerPageDropdown<% end_if %>
				</div>
			</div>
		</div>
	</div>
</div>