import { loginToSite, createNewPost, disableGutenbergFeatures } from '../helpers';
import 'cypress-file-upload';

before( function() {
  loginToSite();
  createNewPost();
  disableGutenbergFeatures();
} );

// Maintain WordPress logged in state
Cypress.Cookies.defaults( {
  whitelist: /wordpress_.*/
} );

// Custom uploadFile command
Cypress.Commands.add( 'uploadFile', ( fileName, fileType, selector ) => {
  cy.get( selector ).then( subject => {
    cy.fixture( fileName, 'hex' ).then( ( fileHex ) => {
      const fileBytes = hexStringToByte( fileHex );
      const testFile = new File( [ fileBytes ], fileName, {
          type: fileType
      } );
      const dataTransfer = new DataTransfer()
      const el = subject[0]

      dataTransfer.items.add(testFile)
      el.files = dataTransfer.files
    } );
  } );
} );

// Utilities
function hexStringToByte( str ) {
  if ( ! str ) {
    return new Uint8Array();
  }

  var a = [];
  for ( var i = 0, len = str.length; i < len; i += 2 ) {
    a.push( parseInt( str.substr( i, 2 ), 16 ) );
  }

  return new Uint8Array( a );
}
