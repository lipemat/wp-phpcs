import DOMPurify from 'dompurify';

const sanitize = DOMPurify.sanitize;

let arbitrary = 'First &middot; Second';

const Test = ( {} ) => {
	// DangerouslySetInnerHTMLSniff.
	return (
		<div>
			<div dangerouslySetInnerHTML={{__html: sanitize( arbitrary )}} />
		</div>
	);


	// HTMLExecutingFunctionsSniff.
	$( body ).after( sanitize( arbitrary ) );
	$( body ).append( sanitize( arbitrary ) );
	$( body ).appendTo( sanitize( arbitrary ) );
	$( body ).before( sanitize( arbitrary ) );
	$( body ).html( sanitize( arbitrary ) );
	$( body ).insertAfter( sanitize( arbitrary ) );
	$( body ).insertBefore( sanitize( arbitrary ) );
	$( body ).prepend( sanitize( arbitrary ) );
	$( body ).prependTo( sanitize( arbitrary ) );
	$( body ).replaceAll( sanitize( arbitrary ) );
	$( body ).replaceWith( sanitize( arbitrary ) );
	$( body ).write( sanitize( arbitrary ) );
	$( body ).writeln( sanitize( arbitrary ) );
	$( body ).writeln( DOMPurify.sanitize( arbitrary ) );

	// InnerHTMLSniff.
	document.getElementById( 'body' ).innerHTML = sanitize( arbitrary );
	document.getElementById( 'body' ).innerHTML = DOMPurify.sanitize( arbitrary );

	// StringConcatSniff.
	const str = 'test' + sanitize( '<concat>' ) + sanitize( 'test' ) + snx( '</concat>' );
	const strv = 'test' + DOMPurify.sanitize( '<concat>' ) + DOMPurify.sanitize( 'test' ) + snx( '</concat>' );

	// StrippingTagsSniff.
	$( body ).html( sanitize( arbitrary ) ).text();
	$( body ).html( DOMPurify.sanitize( arbitrary ) ).text();

	// WindowSniff.
	window.location.href = sanitize( arbitrary );
	window.location.href = DOMPurify.sanitize( arbitrary );
	window.location.protocol = sanitize( arbitrary );
	window.location.host = sanitize( arbitrary );
	window.location.hostname = sanitize( arbitrary );
	window.location.pathname = sanitize( arbitrary );
	window.location.search = sanitize( arbitrary );
	window.location.hash = sanitize( arbitrary );
	window.location.username = sanitize( arbitrary );
	window.location.port = sanitize( arbitrary );
	window.location.password = sanitize( arbitrary );
	window.name = sanitize( arbitrary );
	window.status = sanitize( arbitrary );

	let w = '';
	w = sanitize( window.location.href );
	w = sanitize( window.location.href );
	w = sanitize( window.location.protocol );
	w = sanitize( window.location.host );
	w = sanitize( window.location.hostname );
	w = sanitize( window.location.pathname );
	w = sanitize( window.location.search );
	w = sanitize( window.location.hash );
	w = sanitize( window.location.username );
	w = sanitize( window.location.port );
	w = sanitize( window.location.password );
	w = sanitize( window.name );
	w = sanitize( window.status );

	w = DomPurify.sanitize( window.location.href );
	w = DomPurify.sanitize( window.location.href );
	w = DomPurify.sanitize( window.location.protocol );
	w = DomPurify.sanitize( window.location.host );
	w = DomPurify.sanitize( window.location.hostname );
	w = DomPurify.sanitize( window.location.pathname );
	w = DomPurify.sanitize( window.location.search );
	w = DomPurify.sanitize( window.location.hash );
	w = DomPurify.sanitize( window.location.username );
	w = DomPurify.sanitize( window.location.port );
	w = DomPurify.sanitize( window.location.password );
	w = DomPurify.sanitize( window.name );
	w = DomPurify.sanitize( window.status );
};
