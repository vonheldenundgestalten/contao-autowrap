# contao-autowrap
Contao 4 bundle to wrap content elements for easier page layout and HTML organization.

The extension automatically wraps the same content elements in a div for applying CSS to the collection of items, for example flex or grid based arrangements of the unit. Current structure for the wrap:
```html
<div class="autowrap autowrap-<elementname> autowrap-element-count-<number of elements>">
    <div class="inside">
       <elements>
    </div>
</div>
```

Elements and Aliases *for the same element* can be mixed.

## Usage

* Go to settings and select which elements should be wrapped
* Done :)

<img src="/screenshot-settings.png" title="Screenshot of the Element selection" width="585">

Screenshot of a recent project using autowrap. Don't mind the weird element names, we use a lot of custom content elements ;)

Multiple content elements can be selected - but we only create groups of the same elements within one wrap.
