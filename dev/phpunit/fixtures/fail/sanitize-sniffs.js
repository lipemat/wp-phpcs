import {sanitize} from 'dompurify';

let arbitrary = 'First &middot; Second';

const Test = ( {} ) => {
	// DangerouslySetInnerHTMLSniff.
	return (
		<div>
			<div dangerouslySetInnerHTML={{__html: arbitrary}} />
		</div>
	);


	// HTMLExecutingFunctionsSniff.
	$( body ).after( arbitrary );
	$( body ).append( arbitrary );
	$( body ).appendTo( arbitrary );
	$( body ).before( arbitrary );
	$( body ).html( arbitrary );
	$( body ).insertAfter( arbitrary );
	$( body ).insertBefore( arbitrary );
	$( body ).prepend( arbitrary );
	$( body ).prependTo( arbitrary );
	$( body ).replaceAll( arbitrary );
	$( body ).replaceWith( arbitrary );
	$( body ).write( arbitrary );
	$( body ).writeln( arbitrary );

	// InnerHTMLSniff.
	document.getElementById( 'body' ).innerHTML = arbitrary;

	// StringConcatSniff.
	const str = 'test' + '<concat>' + 'test' + snx( '</concat>' );

	// StrippingTagsSniff.
	$( body ).html( arbitrary ).text();

	// WindowSniff.
	window.location.href = arbitrary;
	window.location.protocol = arbitrary;
	window.location.host = arbitrary;
	window.location.hostname = arbitrary;
	window.location.pathname = arbitrary;
	window.location.search = arbitrary;
	window.location.hash = arbitrary;
	window.location.username = arbitrary;
	window.location.port = arbitrary;
	window.location.password = arbitrary;
	window.name = arbitrary;
	window.status = arbitrary;

	let w = '';
	w = window.location.href;
	w = window.location.href;
	w = window.location.protocol;
	w = window.location.host;
	w = window.location.hostname;
	w = window.location.pathname;
	w = window.location.search;
	w = window.location.hash;
	w = window.location.username;
	w = window.location.port;
	w = window.location.password;
	w = window.name;
	w = window.status;
};
