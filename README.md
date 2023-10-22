# Joomla Article Text Filter plugin

Joomla 4 compatible plugin to filter parts of the text of a Joomla article.

Inspired by the OSD Content Restriction plugin by Open Source Design for Joomla 2.5 and 3. Rewritten for Joomla 4.

IMPORTANT: You cannot have both the old OSD Content Restriction plugin and this new one activated at the same time.
YOUR JOOMLA SITE MIGHT CRASH!

## Usage

The Article Text Filter plugin allows you filter the text in Joomla
article and show or hide parts of a Joomla article based on six
different variables, namely the user group, the user, the author
group, the author, if the article is on the homepage or if it is
featured.

### Filter based on the Joomla! user group

Any content between the tags (plugin syntax) will be rendered
depending on the user group permissions of the logged in user. Some
examples:

```
{user_group 1} Logged in users in user group 1 will see the content {/user_group}
{user_group !1} All users except logged in users in user group 1 will see the content {/user_group}
{user_group 1 || 2} Only logged in users in user group 1 or 2 will see the content {/user_group}
{user_group !1 || !2} all users except in group 1 or 2 will see the content {/user_group}
```

Use the correct user group number depending on your own set-up.

Important note: Joomla permission are hierarchical. If you limit
access to registered users, groups under registered users will also
not have access. If you don't want this, try approaching the situation
by turning it around. i.e. only groups x and y do have access, instead
of z not having access.

Important too: The Guest and Public user group have a user group ID
(in the back-end) but website visitors who are not logged in DO NOT
since they are not logged in! As such use the {guest} syntax as
described below.

### Filter based on Guest/Public (or logged in user).

If you like to show content to non-logged in users, a.k.a. guests, you
can do this using this syntax.

    {guest} ... content for website visitors who are not logged in ... {/guest}
	{!guest} ... content will be displayed to any registered user .... {/guest}

### Filter based on the loged in user id

```
{user 63} Logged in user 63 (where 63 is the Joomla user id) will see the article content between the plugin syntax. {/user}
{user !59} All logged in users except user 59 will see the article content between the plugin syntax. {/user}
{user 59||84} Only logged in users 59 or 84 will see the article content between the plugin syntax. {/user}
{user !59||!84} All logged in users except users 59 or 84 will see the article content between the plugin syntax. {/user}
```

### Filter based on the author group of the Joomla! article

```
{author_group 5} If the author of the article belongs to author group 5, the article content between the plugin tags will be shown.{/author_group}
{author_group !5} If the author of the article does not belong to author group 5, the article content between the plugin tags will be shown.{/author_group}
```

The content will be visible regardless of the user group of the person viewing the website.

### Filter based on the author of the Joomla! article

    {author}Only the logged in author of the Joomla article will see the content between the plugin syntax. There is no need to specify the author id.{/author}

### Filter based on if article is featured

    {featured} ... this content is only shown if the article is set as featured ... {/featured}

Usergroup or authorgroup has no influence on the display

### Filter based on if the Joomla! article is shown on the hompage

    {homepage} ... content just for homepage .... {/homepage}
    {!homepage} ... content not shown on homepage but everywhere else .... {/homepage}

Usergroup or authorgroup has no influence on the display.

## Original

Complete documentation is still available from [Open Source
Design](https://documentation.form2content.com/f2c-extensions/joomla-content-restriction-plugin)
where you also can [download the original
plugin](https://www.form2content.com/download/joomla-content-restriction-plugin/joomla-2-5-3)
from the download page (link on documentation page is broken).

For any further questions on the original OSD Content Restriction plugin, please contact via support@opensourcedesign.nl.
