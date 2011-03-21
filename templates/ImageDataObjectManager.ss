<div id="$id" class="RequestHandler FormField DataObjectManager FileDataObjectManager ImageDataObjectManager $NestedType field" href="$CurrentLink">
	<div class="ajax-loader"></div>
	<div class="dataobjectmanager-actions <% if HasFilter %>filter<% end_if %>">
		<% if Can(upload) %>
			<a class="popup-button" rel="$PopupWidth" href="$UploadLink" alt="add">
				<span class="uploadlink"><img src="dataobject_manager/images/add.png" alt="" /><% sprintf(_t('DataObjectManager.ADDITEM','Add %s',PR_MEDIUM,'Add [name]'),$AddTitle) %></span>
			</a>
		<% else %>
			<h3>$PluralTitle</h3>
		<% end_if %>
	</div>
	<div class="dataobjectmanager-filter">
		<% if HasFilter %>$FilterDropdown<% end_if %>
	</div>
	<div style="clear:both;">&nbsp;</div>
	<div class="top-controls">
		<div class="rounded_table_top_right">
			<div class="rounded_table_top_left">
				
				<div id="size-control-wrap" class="position{$SliderPosition}"><img src="dataobject_manager/images/zoom_out.gif" class="out" /><div class="size-control"></div><img src="dataobject_manager/images/zoom_in.gif" class="in"/></div>
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
					<span class="sbox_l"></span><span class="sbox"><input value="<% if SearchValue %>$SearchValue<% else %><% _t('DataObjectManager.SEARCH','Search') %><% end_if %>" type="text" class="srch_fld"  /></span><span class="sbox_r srch_clear"></span>
				</div>
				<div style="clear:both;"></div>
			</div>
		</div>
	</div>
	<div class="$ListStyle column{$Headings.Count}" class="list-holder" style="width:100%;">
		<div class="dataobject-list">
			<ul <% if ShowAll %>class="sortable-{$sourceClass}"<% end_if %>>
				<% if ListView %>
					<li class="head">
						<div class="fields-wrap">
						<% control Headings %>
						<div class="col $FirstLast">
							<div class="pad">
									<a href="$SortLink">$Title &nbsp;
									<% if IsSorted %>
										<% if SortDirection = ASC %>
										<img src="cms/images/bullet_arrow_up.png" alt="" />
										<% else %>
										<img src="cms/images/bullet_arrow_down.png" alt="" />
										<% end_if %>
									<% end_if %>
									</a>
							</div>
						</div>
						<% end_control %>
						</div>
						<div class="actions col">&nbsp;</div>
					</li>
				<% end_if %>
				<% if Items %>
				<% control Items %>
					<li id="record-$Parent.id-$ID" style="width:{$ImageSize}px; height:{$ImageSize}px;">
								<div class="pad">
									<% if Top.ShowAll %><span class="handle"><img src="dataobject_manager/images/move_icon.jpg" /></span><% end_if %>
									<div class="file-icon"><a href="<% if CanViewOrEdit %>$EditLink<% else %>#<% end_if %>" rel="$PopupWidth" class="popup-button editlink tooltip"><img class="image" src="$FileIcon" alt="" style="width:{$ImageSize}px;" /></a></div>
									<% if Can(delete) %><div class="delete"><a title="<% _t('DataObjectManager.DELETE','Delete') %>" href="$DeleteLink" class="delete-link"><img src="dataobject_manager/images/trash.gif" height="12px" alt="delete" /></a></div><% end_if %>
									<span class="tooltip-info" style="display:none">
										<% control Fields %>
											<strong>$Name</strong>: $Value<% if Last %><% else %><br /><% end_if %>
										<% end_control %>
									</span>
								</div>
						
	
					</li>
				<% end_control %>
				<% else %>
						<li><em><% sprintf(_t('DataObjectManager.NOITEMSFOUND','No items found'),$PluralTitle) %></em></li>
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