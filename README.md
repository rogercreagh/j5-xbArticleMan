# j5-xbArticleMan
#####  Article Manager for Joomla5 to manage links, images, tags and shortcodes in com_content articles. 

The aim is to provide facilities which are missing from core Joomla that can make it difficult keeping track of broken links (internal and external), missing images (internal or external), plugin shortcodes that may be in use, and where tags are used (both in articles and also ny other components).

If you have a decent number of articles xbArticleMan is here to help you find breaking changes when a link becomes non-functional or an image disappears.

------

This is developed from the Joomla 3 version but is reworked as Joomla5 native code - do not attempt to install on J3. It should work okay on J5 without the B/C plugin activated.

It *might* work with Joomla 4 but I absolutely haven't tested it - try it at your own risk, and if it doesn't work then you'll have to update to J5.

------

#### Notes

xbArticleMan includes a simplified editor for articles which only allows editing of a limited number of fields:

- Title and Alias
- Intro and Full article featured images
- Related Item Links (A,B,C)
- Category, Admin note, and published state
- Tags - including the ability to define tag groups under a parent Tag to make it easy to find a tag in a long list. (see below)

Other fields can of course be edited by using the link to the full com_content article editing. From the Shortcodes view the default is to open the full editor as you probably want to edit the description if you have broken shortcodes left after deleting an unwanted plugin. On all other list views the default is to open the quick editor, and the pen icon after the title opens the full editor.

##### NB

xbArticleMan pays no attention to either the new workflows feature or article versioning. It simply ignores these. If you are using Workflows you *might* find that using the quick editor breaks something. In particular an user with editing privileges can change the published status of an article whatever the workflow demands.

##### Tags

As mentioned above there are some additional facilities for tagging. 

Firstly in the xbArticleMan option you can define up to four tags as Group Parents. When a Group Parent is defined then on the Quick Edit view there will be a separate tag box for it which only lists the child tags of the parent (did you even realise that tags can have a hierarchy? )

This is very useful if for example you use people's names as tags - you can separate them out by making them all child tags of a parent tag "People"  and display them as a separate group. 

In the Article Tags view (arttags) the tags for each article are listed with their parent names, sorted by parent. 

A new Tag view accessed by clicking on a tag's label will provide a view listing all items with that tag - including items from other components, and any child tags with counts of items tagged. The items are linked to their edit view

In the Batch operation for all the list views there is an UnTag box to allow you to quickly remove a tag from one or more articles.

A new Tags list view will provide an improved version of the Tags component list view giving counts for each component using the tag, with links to the xbArticleMan detailed tag view and the Tag edit tag view.

##### The eyecon 

Throughout xbComponents and eye icon (eyecon geddit?) after the name of an element links to a modal front-end view of the element. This enables a quick preview of what the item looks like on the frontend of the site to check if it is working correctly.
