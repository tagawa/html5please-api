# Support Browsers with Feature Detection

This project provides an API based on [caniuse](http://caniuse.com)'s API to retrieve browser support information based on features you require on the client-side. 

For example, if you want your page to only render on browsers that support Transitions and Canvas, you can send in an API request to [api.html5please.com](http://api.html5please.com) and print the returned HTML as a message when the page is viewed on browsers that do not support these features. 

- TODO: Add images of how it looks like on browsers with/without support

# Reference

URI Format: http://api.html5please.com/[feature1+feature2..+featureN].[format]?[option1&option2..optionN]

## Features
Any feature that you are looking for. Please look into the `keywords.json` to find out what features are supported. Alternatively you can use the autocomplete on the site to do so.

## Formats
- `html`: the output will be valid HTML with the mimetype of `text/html`. 

- `json`: the output will be valid JSON with the mimetype of `text/json`. 

- `xml`: the output will be valid XML with the mimetype of `text/xml`.

## Options

- `nocss`: the HTML will not include the stylesheet. 

- `text`: the HTML will be optimized for text output. 

- `icon`: the HTML will be optimized for icon output. 

-`supported`: the JSON will only output whether the agent supports the requested features. 

- `noagent`: the JS will return results for all browsers with no agent detection. 

- `callback = [ functionName ]`: the output will be JavaScript, wrapped in this function name.


The site that represents this project runs off `index.html` and references files from `site` folder. It uses SASS so please make any changes to `style.scss` rather than `style.css`.

# Credits

- @fyrd for amazing caniuse API which underlies this work
- @jonathantneal for all the magic with PHP and building a robust API on top of caniuse's data
- @aaronlayton for initial design of html output
- @divya & @paul_irish for corralling the project together


