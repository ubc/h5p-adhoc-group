import React from 'react';
import ReactDOM from 'react-dom';
import ContentEdit from '../assets/src/js/content-edit';

var div = document.getElementById('h5p-taxonomy').children[0];
div.insertAdjacentHTML('beforeend', '<div id="h5p-group" class=\"postbox taxonomy\"></div>');

ReactDOM.render(
	<ContentEdit />,
	// eslint-disable-next-line no-undef
	document.getElementById( 'h5p-group' )
);
