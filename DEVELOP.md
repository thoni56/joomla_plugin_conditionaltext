# How to develop and release

## Developing

T.B.D - tips on actually developing and debuggin...

## Releasing

Update the version numbers in the plugin and update XML:s
(`plugin/articletextfilter.xml` and
`plg_content_articletextfilter-update.xml`) so that they match. You
only need to update actual version in the update manifest.

Create a tag with the same version with a leading `v`, as in `v4.0.1`.

A GitHub action will be triggered that checks consistency, creates a
new GitHub release, packages the plugin and attaches the plugin file
to the release and then also automatically updates the update
manifest.


