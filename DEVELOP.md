# How to develop and release

## Developing

T.B.D - tips on actually developing and debuggin...

## Releasing

Update the version number in the plugin manifest XML
(`plugin/conditionaltext.xml`) and create a tag with the same version
with a leading `v`, as in `v4.0.1`.

A GitHub action (`.github/workflows/main.yml`) will be triggered that
checks consistency, creates a new GitHub release, packages the plugin
and attaches the plugin file to the release and then also
automatically updates the update manifest.


