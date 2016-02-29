## OCEembed - Opencontent oEmbed operators

[oEmbed](http://oembed.com/ "oEmbed site") is a format for allowing an embedded representation of a URL on third party sites.

oEmbed was designed to avoid having to copy and paste HTML from the site hosting the media you wish to embed. It supports videos, images, text, and more. 

This eZ extension - loosely based on the WordPress oemabed implementation - offers the oEmbed easy embedding feature via some template operator.

### Requirements

* eZP >= 4.X or 5.X (when running the Legacy Stack only)

### Installation

Enable the extension; clear all caches

### Preinstalled embed provider:

* blip.tv
* DailyMotion
* Flickr
* FunnyOrDie.com
* Hulu
* Photobucklet
* PollDaddy
* Qik
* Revision3
* Scribd
* Sideshare
* SmugMug
* Ustream
* Viddler
* Vimeo
* Wordpress.tv
* YouTube
* Google Video
* Twitter

You can add your own handler by creating a php class which implements oEmbedProviderInterface and adding a oEmbedProviders[] in ocembed.ini.


## Available template operators

#### autoembed( mixed $separator, hash $parameters )

Print the html value of oEmbed response.

The `separator` and `parameters` variables are not required.

The `separator` value can be a string or an array.
The default `separator` value is `array( '<div class="text-center">', '</div>' )`

The `parameters` value must be an array and overrides the default ocembed.ini width and height parameters [OCEmbedSettings].EmbedDefaults.

Example:
```tpl
{"http://www.slideshare.net/gggeek/ezpublish-meets-simfony2-phpday2013"|autoembed( array( '<div class="media-embed">', '</div>' ), hash( 'width', '425', 'height', '355' ) )}
```

The example returns (without html comments):
```html
<!-- separator -->
<div class="media-embed">
<!-- start of result of oembed fetch -->
<object width="425" height="355" id="__sse21389517">
    <param value="http://static.slidesharecdn.com/swf/ssplayer2.swf?doc=sf2ezpphpday2013-130518043919-phpapp01&amp;stripped_title=ezpublish-meets-simfony2-phpday2013&amp;userName=gggeek" name="movie">
    <param value="true" name="allowFullScreen">
    <param value="always" name="allowScriptAccess">
    <param value="transparent" name="wmode">
    <embed width="425" height="355" wmode="transparent" allowfullscreen="true" allowscriptaccess="always" type="application/x-shockwave-flash" src="http://static.slidesharecdn.com/swf/ssplayer2.swf?doc=sf2ezpphpday2013-130518043919-phpapp01&amp;stripped_title=ezpublish-meets-simfony2-phpday2013&amp;userName=gggeek" name="__sse21389517">
</object> <!-- end of result of oembed fetch -->
<!-- end separator -->
</div>
```

This operator is used in the eztext.tpl and ezxmltext.tpl override templates include in this extension:
```tpl
{* ocembed/design/standard/templates/content/datatype/view/eztext.tpl *}
{$attribute.content.output.output_text|autoembed()}
```

#### search_embed()

Return all embeddable links found in passed text.

Example:
```tpl
{def $test = $my_long_text_full_of_links|search_embed()}
```
Returns an array of embeddable urls.


#### get_oembed_object( string $url, hash $parameters )

Return the oEmbed response

The `url` parameter is required and must be an "oembeddable" url.

The `parameters` value must be an array and overrides the default ocembed.ini width and height parameters [OCEmbedSettings].EmbedDefaults.

Example:
```tpl
{def $test = get_oembed_object("http://www.slideshare.net/gggeek/ezpublish-meets-simfony2-phpday2013")}
```

The $test variable contains an associative array of oembed response object.

### Cache data

If ```ocembed.ini [OCEmbedSettings] Cache``` is enabled, all ocembed results are stored in ezsite_data table under the key 'oembed_cached_data'.
To clear that cache, you can run ```php extension/ocembed/bin/php/clear_cache_data.php -s<siteaccess>```
