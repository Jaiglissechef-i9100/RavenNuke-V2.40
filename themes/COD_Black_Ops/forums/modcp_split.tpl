<form method="post" action="{S_SPLIT_ACTION}">
<table width="100%" cellspacing="2" cellpadding="2" border="0">
<tr>
<td class="nav"><a href="{U_INDEX}">{L_INDEX}</a> &raquo; <a href="{U_VIEW_FORUM}">{FORUM_NAME}</a></td>
</tr>
</table>
<table width="100%" cellpadding="4" cellspacing="1" border="0" class="forumline">
<tr>
<td class="cat" align="center" colspan="3"><span class="forumstitle">{L_SPLIT_TOPIC}</span></td>
</tr>
<tr>
<td class="row1" colspan="3" align="center"><span class="genmed">{L_SPLIT_TOPIC_EXPLAIN}</span></td>
</tr>
<tr>
<td class="row1" nowrap="nowrap">{L_SPLIT_SUBJECT}:</td>
<td class="row2" colspan="2"><input type="text" size="35" style="width: 350px" maxlength="100" name="subject" class="post" />
</td>
</tr>
<tr>
<td class="row1" nowrap="nowrap">{L_SPLIT_FORUM}:</td>
<td class="row2" colspan="2">{S_FORUM_SELECT}</td>
</tr>
<tr align="center">
<td class="cat" colspan="3">
<input class="catbutton" type="submit" name="split_type_all" value="{L_SPLIT_POSTS}" />
&nbsp;&nbsp;&nbsp;&nbsp;
<input class="catbutton" type="submit" name="split_type_beyond" value="{L_SPLIT_AFTER}" />
</td>
</tr>
<tr>
<td align="center" class="cat"><span class="forumstitle">{L_AUTHOR}</span></td>
<td align="center" class="cat"><span class="forumstitle">{L_MESSAGE}</span></td>
<td align="center" class="cat"><span class="forumstitle">{L_SELECT}</span></td>
</tr>
<!-- BEGIN postrow -->
<tr>
<td valign="top" class="{postrow.ROW_CLASS}"><span class="name"><a name="{postrow.U_POST_ID}"></a>{postrow.POSTER_NAME}</span>
<img src="themes/COD_Black_Ops/forums/images/spacer.gif" alt="" width="150" height="1" />
</td>
<td width="100%" valign="top" class="{postrow.ROW_CLASS}">
<table width="100%" cellspacing="0" cellpadding="3" border="0">
<tr>
<td class="postdetails"><img src="themes/COD_Black_Ops/forums/images/icon_minipost.gif" alt="{L_POST}" title="{L_POST}" width="12" height="9" />{L_POSTED}:
{postrow.POST_DATE}&nbsp;&nbsp;&nbsp;&nbsp;{L_POST_SUBJECT}: {postrow.POST_SUBJECT}</td>
</tr>
<tr>
<td valign="top" class="postbody">
<hr />
{postrow.MESSAGE}</td>
</tr>
</table>
</td>
<td width="5%" align="center" class="{postrow.ROW_CLASS}">{postrow.S_SPLIT_CHECKBOX}</td>
</tr>
<tr>
<td colspan="3" height="1" class="spacerow"><img src="themes/COD_Black_Ops/forums/images/spacer.gif" width="1" height="1" alt="" /></td>
</tr>
<!-- END postrow -->
<tr>
<td class="cat" colspan="3" align="center">
<input class="catbutton" type="submit" name="split_type_all" value="{L_SPLIT_POSTS}" />
&nbsp;&nbsp;&nbsp;&nbsp;
<input class="catbutton" type="submit" name="split_type_beyond" value="{L_SPLIT_AFTER}" />
{S_HIDDEN_FIELDS}</td>
</tr>
</table>
<table width="100%" cellspacing="2" cellpadding="2" border="0">
<tr>
<td class="nav"><a href="{U_INDEX}">{L_INDEX}</a> &raquo; <a href="{U_VIEW_FORUM}">{FORUM_NAME}</a></td>
</tr>
</table>
</form>
