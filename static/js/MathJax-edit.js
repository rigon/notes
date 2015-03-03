/**
 * MathJax-edit/MathJax-edit.js
 *
 * The MathJax-edit functionality, defines the MathJaxCorrection class
 *
 * This software is free software; you may redistribute it and/or modify it under the terms
 * of the GNU General Public License as published by the Free Software Foundation Version 2,
 * June 1991, aka "copyleft" or the GPL.
 *
 * This software is distributed in the hope that it will be useful, but without any warranty;
 * without even the implied warranty of merchantability or fitness for a particular purpose.
 * See the GNU General Public License for more details.
 *
 * Along with this software you should have received a copy of the GNU General
 * Public License. If not, see http://www.gnu.org/licenses/old-licenses/gpl-2.0.html.
 *
 * MathJax-edit, Copyright © 2012-2013 Simon Pepping,
 * License: GNU General Public License Version 2, June 1991.
 *
 * $Format:%an %ai$
 */

/**
 * Represents a MathJaxCorrection error; subclass of Error.
 * @constructor
 * @access public
 * @param {MathJaxCorrection.error} code a value from MathJaxCorrection.error
 * @param {string} message the error message
 * @property name {string} fixed value "MathJaxCorrectionError"
 * @property code {MathJaxCorrection.error} the error code
 * @property message {string} the error message
 **/
function MathJaxCorrectionError(code, message) {
	this.name = "MathJaxCorrectionError";
	this.code = code;
	this.message = message;
}
MathJaxCorrectionError.prototype = new Error();
MathJaxCorrectionError.prototype.constructor = MathJaxCorrectionError;

/********************************* public methods *********************************/

/**
 * Represents a correction on a formula rendered by MathJax;
 * @constructor
 * @access public
 */
function MathJaxCorrection() {
	this._initialize();
}

/**
 * status values
 * @enum {int}
 * @access public
 * @readonly
 */
MathJaxCorrection.status = {INITIALIZED: 0, HAS_SELECTION: 1, APPLIED_CORRECTION: 2};
/**
 * return values
 * @enum {int}
 * @access public
 * @readonly
 */
MathJaxCorrection.result = {SUCCESS: 0, ERROR: 1};
/**
 * errors
 * @enum {int}
 * @access public
 * @readonly
 */
MathJaxCorrection.error = {
	NOT_INITIALIZED: 0,
	NO_SELECTION: 1,
	SELECTION_NOT_IN_MATH: 2,
	NO_CORRECTION: 3,
	CORRECTION_FAILED: 4,
	RANGE_ERROR: 5,
	PARSE_ERROR: 6,
	SELECTED_NOT_FOUND: 7,
	NO_ACTION: 8,
	};
/**
 * mime types
 * @enum {string}
 * @access public
 * @readonly
 */
MathJaxCorrection.MathJaxMimeTypes
	= {MATHML: "math/mml",
	   TEX: "math/tex",
	   ASCIIMATH: "math/asciimath"};
/**
 * namespaces
 * @enum {string}
 * @access public
 * @readonly
 */
MathJaxCorrection.namespaces
	= {DEFAULT: "",
	   MATHML: "http://www.w3.org/1998/Math/MathML"};

/**
 * Initialization
 * @access private
 * @method
 * @property range {Range} the range
 * @property originalSelectedText {string} the originally selected text
 * @property mathmlToHtmlMap {Map} map from source MathML to display HTML
 * @property originalFormulaHTML {string} the original formula in HTML
 * @property expandedSelectedText {string} the expanded selected text
 * @property status {MathJaxCorrection.status} a value from MathJaxCorrection.status
 * @property mathDoc {Document} the MathML DOM
 * @property mathRange {Range} the range in MathML
 * @property xpath {string} the XPath to the selection
 * @property selectionLength {int} the number of top level elements in the selection
 * @property correctionDoc {Document} the correction DOM
 * @property correctionTopNode {Element} the top node of the correction in the correction DOM
 * @property sourceScript {Element} the source script element
 * @property sourceDoc {Document} the source DOM
 * @property selectedNode {Element} the selected element
 * @property deletedDoc {Document} the deleted DOM
 */
MathJaxCorrection.prototype._initialize = function() {
	this.range = null, this.originalSelectedText = null, this.mathmlToHtmlMap = null,
	this.originalFormulaHTML = null, this.expandedSelectedText = null,
	this.status = MathJaxCorrection.status.INITIALIZED,
	// selection
	this.mathDoc = null, this.mathRange = null, this.xpath = null, this.selectionLength = 0,
	// correction
	this.correctionDoc = null, this.correctionTopNode = null,
	// source
	this.sourceScript = null, this.sourceDoc = null; this.selectedNode = null,
	// deletion
	this.deletedDoc = null;
}

/**
 * Reset correction
 * @access private
 * @method
 */
MathJaxCorrection.prototype._resetCorrection = function() {
	// correction
	this.correctionDoc = null, this.correctionTopNode = null,
	// deletion
	this.deletedDoc = null,
	this.status = MathJaxCorrection.status.HAS_SELECTION;
}

/**
 * Is status at or past selection?
 * @access private
 * @method
 * @returns {Boolean} whether the status is MathJaxCorrection.status.HAS_SELECTION or higher
 */
MathJaxCorrection.prototype._hasSelection = function() {
	return this.status >= MathJaxCorrection.status.HAS_SELECTION;
}

/**
 * Has correction been applied?
 * @access private
 * @method
 * @returns {Boolean} whether the status is MathJaxCorrection.status.APPLIED_CORRECTION or higher
 */
MathJaxCorrection.prototype._appliedCorrection = function() {
	return this.status >= MathJaxCorrection.status.APPLIED_CORRECTION;
}

/**
 * Read and process the selection (step 1)
 * @method
 * @access public
 * @param range the range to be read; if not given or null, the document will be asked for the selection
 * @throws {MathJaxCorrectionError} MathJaxCorrection.error.SELECTION_NOT_IN_MATH
 * @throws {MathJaxCorrectionError} MathJaxCorrection.error.RANGE_ERROR
 */
MathJaxCorrection.prototype.readSelection = function(range)
{
	
	// if a selection has been made, throw it away and reinitialize
	if (this._hasSelection()) {
		this._initialize();
	}

	if (range) {
		this.range = range;
	} else {
		this.range = document.getSelection().getRangeAt(0);
	}

	// 1. Get and sanitize selected range
	if (!this._rangeIsInMath()) {
		this.range = null;
		throw new MathJaxCorrectionError(MathJaxCorrection.error.SELECTION_NOT_IN_MATH, "Selection not in Math");
	}
	
	try {
		this._storeSelectedText();
		// MathJax cannot find the source script if the start- or endContainer is a text node
		this._inOrExcludeTextNode();
		this._storeSourceScriptAndDoc();
		
		// 2. Get range in mathml formula
		this._getMathDocAndRange();
	
		// 3. Sanitize selected range
		this._sanitizeMathDoc();
		this._sanitizeMathRange();
		this._changeHTMLSelection();
		this._storeExpandedSelectedText();
	
		// 4. Get XPath for selection
		this._getXPath();
		this._findSelectedNode();
	} catch(e) {
		this._initialize;
		throw e;
	}
	
	this.status = MathJaxCorrection.status.HAS_SELECTION;
}

/**
 * Accessor for the source script element
 * @method
 * @access public
 * @returns {Element} the source script element
 * @throws {MathJaxCorrectionError} MathJaxCorrection.error.NO_SELECTION
 */
MathJaxCorrection.prototype.getSourceScript = function()
{
	if (!this._hasSelection()) {
		throw new MathJaxCorrectionError(MathJaxCorrection.error.NO_SELECTION, "No selection");
	}
	return this.sourceScript;
}

/**
 * Accessor for the source DOM
 * @method
 * @access public
 * @returns the source DOM
 * @throws {MathJaxCorrectionError} MathJaxCorrection.error.NO_SELECTION
 */
MathJaxCorrection.prototype.getSourceDoc = function()
{
	if (!this._hasSelection()) {
		throw new MathJaxCorrectionError(MathJaxCorrection.error.NO_SELECTION, "No selection");
	}
	return this.sourceDoc;
}

/**
 * Accessor for the original formula HTML
 * @method
 * @access public
 * @returns the original formula HTML
 * @throws {MathJaxCorrectionError} MathJaxCorrection.error.NO_SELECTION
 */
MathJaxCorrection.prototype.getOriginalFormulaHTML = function()
{
	if (!this._hasSelection()) {
		throw new MathJaxCorrectionError(MathJaxCorrection.error.NO_SELECTION, "No selection");
	}
	return this.originalFormulaHTML;
}

/**
 * Accessor for the original formula HTML
 * @method
 * @access public
 * @returns originally selected HTML
 * @throws {MathJaxCorrectionError} MathJaxCorrection.error.NO_SELECTION
 */
MathJaxCorrection.prototype.getSelectedText = function()
{
	if (!this._hasSelection()) {
		throw new MathJaxCorrectionError(MathJaxCorrection.error.NO_SELECTION, "No selection");
	}
	return this.originalSelectedText;
}

/**
 * Accessor for the original formula HTML
 * @method
 * @access public
 * @returns the reconstructed formula
 * @throws {MathJaxCorrectionError} MathJaxCorrection.error.NO_SELECTION
 */
MathJaxCorrection.prototype.getReconstructedFormula = function()
{
	if (!this._hasSelection()) {
		throw new MathJaxCorrectionError(MathJaxCorrection.error.NO_SELECTION, "No selection");
	}
	var serializer = new XMLSerializer();
	return serializer.serializeToString(this.mathDoc);
}

/**
 * Accessor for the original formula HTML
 * @method
 * @access public
 * @returns the selected formula
 * @throws {MathJaxCorrectionError} MathJaxCorrection.error.NO_SELECTION
 */
MathJaxCorrection.prototype.getSelectedFormula = function()
{
	if (!this._hasSelection()) {
		throw new MathJaxCorrectionError(MathJaxCorrection.error.NO_SELECTION, "No selection");
	}
	var serializer = new XMLSerializer();
	for (var i = this.mathRange.startOffset, text = ''; i < this.mathRange.endOffset; ++i) {
		text += serializer.serializeToString(this.mathRange.startContainer.childNodes[i]);
	}
	return text;
}

/**
 * Accessor for the original formula HTML
 * @method
 * @access public
 * @returns the expanded selected text
 * @throws {MathJaxCorrectionError} MathJaxCorrection.error.NO_SELECTION
 */
MathJaxCorrection.prototype.getExpandedSelectedText = function()
{
	if (!this._hasSelection()) {
		throw new MathJaxCorrectionError(MathJaxCorrection.error.NO_SELECTION, "No selection");
	}
	return this.expandedSelectedText;
}

/**
 * Apply the correction (step 2)
 * @method
 * @access public
 * @param correctionText {string} the text of the correction
 * @param correctionType {MathJaxCorrection.MathJaxMimeTypes} the correction type, a value from MathJaxCorrection.MathJaxMimeTypes
 * @param updateSourceScript {boolean} whether the source script element should be updated
 * @throws {MathJaxCorrectionError} MathJaxCorrection.error.NO_SELECTION
 * @throws {MathJaxCorrectionError} MathJaxCorrection.error.CORRECTION_FAILED
 * @throws {MathJaxCorrectionError} MathJaxCorrection.error.NO_ACTION
 * @throws {MathJaxCorrectionError} MathJaxCorrection.error.PARSE_ERROR
 * @throws {MathJaxCorrectionError} MathJaxCorrection.error.SELECTED_NOT_FOUND
 */
MathJaxCorrection.prototype.applyCorrection = function(correctionText, correctionType, updateSourceScript)
{
	
	if (!this._hasSelection()) {
		throw new MathJaxCorrectionError(MathJaxCorrection.error.NO_SELECTION, "No selection");
	}
	if (this._appliedCorrection()) {
		throw new MathJaxCorrectionError(MathJaxCorrection.error.NO_ACTION, "Correction already applied; nothing done");
	}
	
	if (updateSourceScript == null) {
		updateSourceScript = true;
	}
	
	try {
		// 1. read the correction and create the correction document
		this._createCorrectionDoc(correctionText, correctionType, parent);
		
		// 3. remove the selected node from the source document, and create the deleted document
		this._removeSelected();
		
		// 4. insert the correction in the source script
		this._insertCorrection();
	} catch(e) {
		this._resetCorrection();
		throw e;
	}
	
	this.status = MathJaxCorrection.status.APPLIED_CORRECTION;
	
	// if requested by the user, update the source script
	if (updateSourceScript) {
		this.updateSourceScript();
	}
	
}

/**
 * Update source script element
 * @method
 * @access public
 * @returns {MathJaxCorrection.result} MathJaxCorrection.result.SUCCESS
 * @throws {MathJaxCorrectionError} MathJaxCorrection.error.NO_CORRECTION
 */
MathJaxCorrection.prototype.updateSourceScript = function()
{
	if (!this._appliedCorrection()) {
		throw new MathJaxCorrectionError(MathJaxCorrection.error.NO_CORRECTION, "No correction");
	}
	var serializer = new XMLSerializer();
	var text = serializer.serializeToString(this.getSourceDoc());
	this.sourceScript.innerHTML = text;
	return MathJaxCorrection.result.SUCCESS;
}

/**
 * Accessor for the correction DOM
 * @method
 * @access public
 * @returns the correction DOM
 * @throws {MathJaxCorrectionError} MathJaxCorrection.error.NO_CORRECTION
 */
MathJaxCorrection.prototype.getCorrectionDoc = function()
{
	if (!this._appliedCorrection()) {
		throw new MathJaxCorrectionError(MathJaxCorrection.error.NO_CORRECTION, "No correction");
	}
	return this.correctionDoc;
}

/**
 * Accessor for the deleted DOM
 * @method
 * @access public
 * @returns the deleted DOM
 * @throws {MathJaxCorrectionError} MathJaxCorrection.error.NO_CORRECTION
 */
MathJaxCorrection.prototype.getDeletedDoc = function()
{
	if (!this._appliedCorrection()) {
		throw new MathJaxCorrectionError(MathJaxCorrection.error.NO_CORRECTION, "No correction");
	}
	return this.deletedDoc;
}

/**************************************** private methods *********************************/

/**
 * In- or exclude the text node if it contains the start or end of the range
 * @access private
 * @method
 */
MathJaxCorrection.prototype._inOrExcludeTextNode = function() {
	if (this.range.startContainer.nodeType == Node.TEXT_NODE) {
		var parentNode = this.range.startContainer.parentElement;
		// complete text node is included or excluded
		var parentStartOffset = (this.range.startOffset == this.range.startContainer.length ? 1 : 0);
		this.range.setStart(parentNode, parentStartOffset);
	}
	if (this.range.endContainer.nodeType == Node.TEXT_NODE) {
		var parentNode = this.range.endContainer.parentElement;
		// complete text node is included or excluded
		var parentEndOffset = (this.range.endOffset == 0 ? 0 : 1);
		this.range.setEnd(parentNode, parentEndOffset);
	}
}

/**
 * Is the range in a math formula?
 * @access private
 * @method
 * @returns {boolean} whether the range is in a math formula
 */
MathJaxCorrection.prototype._rangeIsInMath = function() {
	return (MathJaxCorrection.eltIsInMath(this.range.startContainer)
		&& MathJaxCorrection.eltIsInMath(this.range.endContainer));
}

/**
 * Is the element in a math formula?
 * @method
 * @static
 * @access public
 * @param elt {Node} the DOM element to be tested
 * @returns {boolean} whether the element is in a math formula
 */
MathJaxCorrection.eltIsInMath = function(elt) {
	for (; elt; elt=elt.parentElement) {
		if (elt.nodeType == Node.ELEMENT_NODE && elt.classList.contains("math")) {
			break;
		}
	}
	return (elt != undefined);
}

/**
 * Store the source script element and DOM
 * @access private
 * @method
 * @throws {MathJaxCorrectionError} MathJaxCorrection.error.PARSE_ERROR
 */
MathJaxCorrection.prototype._storeSourceScriptAndDoc = function() {
	this.sourceScript = MathJax.Hub.getJaxFor(this.range.startContainer).SourceElement();
	this.sourceDoc = new DOMParser().parseFromString(this.sourceScript.innerHTML, "text/xml");
	if (this.sourceDoc.documentElement.nodeName == "parsererror") {
		throw new MathJaxCorrectionError(MathJaxCorrection.error.PARSE_ERROR, "Parse error");
	}
	this.originalFormulaHTML = this.sourceScript.innerHTML;
}

/**
 * Store the selected text
 * @access private
 * @method
 */
MathJaxCorrection.prototype._storeSelectedText = function() {
	var docFragment = this.range.cloneContents();
	this.originalSelectedText = docFragment.textContent;
}

/**
 * Create and store the MathML DOM and range
 * @access private
 * @method
 * @throws {MathJaxCorrectionError} MathJaxCorrection.error.RANGE_ERROR
 */
MathJaxCorrection.prototype._getMathDocAndRange = function() {
	var outputElt = this.range.startContainer;
	while (!outputElt.classList.contains("math")) {
		outputElt = outputElt.parentNode;
	}
	// alert('outputElt: ' + outputElt.textContent);
	
	this._elementToMathmlWithRange(outputElt);
}

/**
 * Create the MathML DOM and range for the input element and its subtree;
 * calls itself recursively for the subtree
 * @access private
 * @method
 * @param elt {Node} the input element
 * @param cursor {Element} the current element in the new MathML DOM
 * @throws {MathJaxCorrectionError} MathJaxCorrection.error.RANGE_ERROR
 */
MathJaxCorrection.prototype._elementToMathmlWithRange = function(elt, cursor)
{
	if (this.mathDoc == null) {
		this.mathDoc = elt.ownerDocument.implementation.createDocument("", "", null);
		this.mathRange = this.mathDoc.createRange();
		this.mathRange.setStart(this.mathDoc, 0);
		this.mathRange.setEnd(this.mathDoc, 0);
		this.mathmlToHtmlMap = new Array();
		cursor = this.mathDoc;
	}
	var imageChar = null;
	var eltIsStartContainer = (elt == this.range.startContainer);
	var startOffset = this.range.startOffset;
	var eltIsEndContainer = (elt == this.range.endContainer);
	var endOffset = this.range.endOffset;
	if (elt.nodeType == Node.TEXT_NODE) {
		var newNode = this.mathDoc.createTextNode(elt.data);
		cursor.appendChild(newNode);
		if (eltIsStartContainer) {
			this.mathRange.setStart(newNode, startOffset);
		}
		if (eltIsEndContainer) {
			this.mathRange.setEnd(newNode, endOffset);
		}
	} else if (elt.nodeType == Node.ELEMENT_NODE && elt.localName == 'img') {
		// TODO check how to get the character value of the image
		var char = elt.getAttribute('src');
		var startIndex = char.length - 'UUUU.png'.length;
		char = char.slice(startIndex, startIndex + 'UUUU'.length);
		imageChar = String.fromCharCode(parseInt(char, 16));
	} else if (elt.nodeType == Node.ELEMENT_NODE) {
		var className = elt.className;
		var classList = elt.classList;
		var isMathmlElt = (className != undefined && className != ""
				   && className != "MathJax" && ! classList.contains("inferred"));
		if (isMathmlElt) {
			var newNode = this.mathDoc.createElementNS(this.sourceDoc.documentElement.namespaceURI, elt.className);
			cursor.appendChild(newNode);
			this.mathmlToHtmlMap.push(new Array(newNode, elt));
			cursor = newNode;
		}
		var children = elt.childNodes;
		var imageTextNode = null;
		for (var i = 0; i < children.length; ++i) {
			if (eltIsStartContainer && i == startOffset) {
				this.mathRange.setStart(cursor, cursor.childNodes.length);
			}
			if (eltIsEndContainer && i == endOffset) {
				this.mathRange.setEnd(cursor, cursor.childNodes.length);
			}
			var c = this._elementToMathmlWithRange(children[i],cursor);
			imageTextNode = this._updateImageText(c, imageTextNode,
							  eltIsStartContainer && i == startOffset,
							  eltIsEndContainer && i == endOffset,
							  cursor);
		}
		if (eltIsStartContainer && children.length == startOffset) {
			this.mathRange.setStart(cursor, cursor.childNodes.length);
		}
		if (eltIsEndContainer && children.length == endOffset) {
			this.mathRange.setEnd(cursor, cursor.childNodes.length);
		}
	}
	return imageChar;
}

MathJaxCorrection.prototype._updateImageText = function(imageChar, imageTextNode, childIsStart, childIsEnd, cursor) {
	// we received the last character; delete reference to the text node
	if (imageChar == null && imageTextNode != null) {
		imageTextNode = null;
	} else if (imageChar != null) {
		// we received the first character; create and append the text node
		if (imageTextNode == null) {
			imageTextNode = this.mathDoc.createTextNode(imageChar);
			cursor.appendChild(imageTextNode);
		}
		// we received a following character
		else {
			imageTextNode.appendData(imageChar);
			// if this image started or ended the range, move it to before the text node
			if (childIsStart) {
				this.mathRange.setStart(cursor, cursor.childNodes.length - 1);
			}
			if (childIsEnd) {
				this.mathRange.setEnd(cursor, cursor.childNodes.length - 1);
			}
		}
	}
	return imageTextNode;	
}

/**
 * Remove text nodes from mixed content
 * Such text nodes result from padding in the HTML and are not part of the MathML formula
 * @access private
 * @method
 */
MathJaxCorrection.prototype._sanitizeMathDoc = function(node)
{
	if (node == null) {
		node = this.mathDoc;
	}
	var hasMixedContent
		= (node.childElementCount != 0
           && node.childNodes.length != node.childElementCount);
	var childNode = node.firstChild;
	while (childNode != null) {
		var nextNode = childNode.nextSibling;
		if (childNode.nodeType == Node.TEXT_NODE && hasMixedContent) {
				node.removeChild(childNode);
		}
		if (childNode.nodeType == Node.ELEMENT_NODE) {
			this._sanitizeMathDoc(childNode);
		}
		childNode = nextNode;
	}
}

/**
 * Modify the MathML range so that it contains complete MathML elements
 * @access private
 * @method
 * @throws {MathJaxCorrectionError} MathJaxCorrection.error.RANGE_ERROR
 */
MathJaxCorrection.prototype._sanitizeMathRange = function()
{
	// Range must contain complete text nodes
	// This has also been done in the range, so it should not have effect
	if (this.mathRange.startContainer.nodeType == Node.TEXT_NODE) {
		if (this.mathRange.startOffset != this.mathRange.startContainer.length) {
			this.mathRange.setStartBefore(this.mathRange.startContainer.parentElement);
		} else {
			this.mathRange.setStartAfter(this.mathRange.startContainer.parentElement);
		}
	}
	if (this.mathRange.endContainer.nodeType == Node.TEXT_NODE) {
		if (this.mathRange.endOffset == 0) {
			this.mathRange.setEndBefore(this.mathRange.endContainer.parentElement);
		} else {
			this.mathRange.setEndAfter(this.mathRange.endContainer.parentElement);
		}
	}

	// Range must not just contain text nodes
	if (this.mathRange.startContainer.childNodes[0].nodeType == Node.TEXT_NODE) {
		if (this.mathRange.startOffset == 0) {
			this.mathRange.setStartBefore(this.mathRange.startContainer);
		} else {
			this.mathRange.setStartAfter(this.mathRange.startContainer);
		}
	}
	if (this.mathRange.endContainer.childNodes[0].nodeType == Node.TEXT_NODE) {
		if (this.mathRange.endOffset == 0) {
			this.mathRange.setEndBefore(this.mathRange.endContainer);
		} else {
			this.mathRange.setEndAfter(this.mathRange.endContainer);
		}
	}

	// If at end of start container or at start of end container,
	// move to next or previous element sibling
	if (this.mathRange.startOffset == this.mathRange.startContainer.childElementCount) {
		var node = this.mathRange.startContainer;
		while (node.nextElementSibling == undefined && node.parentNode) {
			node = node.parentNode;
		}
		this.mathRange.setStart(node,0);
	}
	if (this.mathRange.endOffset == 0) {
		var node = this.mathRange.endContainer;
		while (node.previousElementSibling == undefined && node.parentNode) {
			node = node.parentNode;
		}
		this.mathRange.setEnd(node,node.childElementCount);
	}

	// siblings

	// From Dimitre Novatchev, http://stackoverflow.com/questions/8742002/finding-the-lowest-common-ancestor-of-an-xml-node-set:
	// $n1/ancestor-or-self::node()[exists(. intersect $n2/ancestor-or-self::node())][1]"

	// DOM + XPath implementation
	// commonAncestor: reverse(intersect(startContainer/ancestor-or-self::*, endContainer/ancestor-or-self::*))[1]?
	// startChild: intersect(commonAncestor/*, startContainer/ancestor-or-self::*)[1]
	// endChild: intersect(commonAncestor/*, endContainer/ancestor-or-self::*)[1]

	// javascript implementation
	var startAncestors = new Array(), endAncestors = new Array();
	var commonAncestor;
	for (var node = this.mathRange.startContainer; node; node = node.parentNode) {
		startAncestors.push(node);
	}
	for (var node = this.mathRange.endContainer; node; node = node.parentNode) {
		endAncestors.push(node);
	}
	for (var i = 0; i < startAncestors.length; ++i) {
		for (var j = 0; j < endAncestors.length && !commonAncestor; ++j) {
			if (startAncestors[i] == endAncestors[j]) {
				commonAncestor = startAncestors[i];
				if (i != 0) {
					this.mathRange.setStartBefore(startAncestors[i-1]);
				}
				if (j != 0) {
					this.mathRange.setEndAfter(endAncestors[j-1]);
				}
			}
		}
	}
	if (commonAncestor == undefined) {
		throw new MathJaxCorrectionError(MathJaxCorrection.error.RANGE_ERROR, "No common ancestor found");
	}
}

/**
 * Change the HTML range according to the modified MathML range
 * @access private
 * @method
 */
MathJaxCorrection.prototype._changeHTMLSelection = function() {
	// Change HTML selection
	var startChild, endChild;
	for (var i = 0, commonAncestor = undefined;
		 i < this.mathmlToHtmlMap.length && !(commonAncestor && startChild && endChild);
		 ++i) {
		if (this.mathmlToHtmlMap[i][0] == this.mathRange.startContainer) {
			commonAncestor = this.mathmlToHtmlMap[i][1];
		}
		if (this.mathmlToHtmlMap[i][0] == this.mathRange.startContainer.childNodes[this.mathRange.startOffset]) {
			startChild = this.mathmlToHtmlMap[i][1];
		}
		if (this.mathmlToHtmlMap[i][0] == this.mathRange.endContainer.childNodes[this.mathRange.endOffset - 1]) {
			endChild = this.mathmlToHtmlMap[i][1];
		}
	}
	this.range.setStartBefore(startChild);
	this.range.setEndAfter(endChild);
}

/**
 * Store the text of the expanded selection
 * @access private
 * @method
 */
MathJaxCorrection.prototype._storeExpandedSelectedText = function() {
	var docFragment = this.range.cloneContents();
	this.expandedSelectedText = docFragment.textContent;
}

/**
 * Determine and store the XPath of the MathML range
 * @access private
 * @method
 */
MathJaxCorrection.prototype._getXPath = function() {
	var startNode = this.mathRange.startContainer.childNodes[this.mathRange.startOffset];
	var endNode = this.mathRange.endContainer.childNodes[this.mathRange.endOffset - 1];
	this.xpath = MathJaxCorrection.getXPathForElement(startNode);
	this.selectionLength = 1;
	var sibling = startNode;
	while (sibling != endNode) {
		this.selectionLength += 1;
		sibling = sibling.nextElementSibling;
	}
}

/**
 * Get the XPath for the input element from the top of its owner document is calculated
 * from https://developer.mozilla.org/en-US/docs/Using_XPath
 * @static
 * @method
 * @access public
 * @param elt {Node} the input element
 */
MathJaxCorrection.getXPathForElement = function(elt) {
    var xpath = '';
	var doc = elt.ownerDocument;
    
    do {       
        var pos = 0;
        var prev = elt;
        while (prev) {
            if (prev.nodeName === elt.nodeName) { // If it is ELEMENT_NODE of the same name
                pos += 1;
            }
            prev = prev.previousElementSibling;
        }
        
        xpath = "*[name()='" + elt.nodeName + "' and namespace-uri()='" + (elt.namespaceURI===null?'':elt.namespaceURI) + "'][" + pos + ']' + (xpath==''?'':'/') + xpath;
		
        elt = elt.parentNode;
    } while (elt != doc);
	xpath = "/" + xpath;

    return xpath;
}

/**
 * Determine and store the selected node
 * @access private
 * @method
 * @throws {MathJaxCorrectionError} MathJaxCorrection.error.SELECTED_NOT_FOUND
 */
MathJaxCorrection.prototype._findSelectedNode = function()
{
	this.selectedNode = this.sourceDoc
		.evaluate(this.xpath, this.sourceDoc, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null).singleNodeValue;
	if (this.selectedNode == null) {
		throw new MathJaxCorrectionError(MathJaxCorrection.error.SELECTED_NOT_FOUND, "Selected Node was not found");
	}
}

/**
 * display attributes
 * @enum {string}
 * @access private
 * @readonly
 */
MathJaxCorrection.displayAttr
	= {MathML: " display='block'",
	   TeX: "; mode=display",
	   AsciiMath: ""};

/**
 * Create the correction DOM
 * @access private
 * @method
 * @param correctionText {string} the text of the correction
 * @param correctionType {MathJaxCorrection.MathJaxMimeTypes} the correction type, a value from MathJaxCorrection.MathJaxMimeTypes
 * @throws {MathJaxCorrectionError} MathJaxCorrection.error.CORRECTION_FAILED
 * @throws {MathJaxCorrectionError} MathJaxCorrection.error.PARSE_ERROR
 */
MathJaxCorrection.prototype._createCorrectionDoc = function(correctionText, correctionType)
{
	if (correctionType == MathJaxCorrection.MathJaxMimeTypes.MATHML) {
		correctionText = this._parseAndFixMathml(correctionText);
	}
	var parent = this.sourceScript.parentNode;
	var correctionMathml = MathJaxCorrection.OtherToMathml(correctionText, correctionType, parent);
	var mmlCorrectionDoc = new DOMParser().parseFromString(correctionMathml, "text/xml");
	if (mmlCorrectionDoc.documentElement.nodeName == "parsererror") {
		throw new MathJaxCorrectionError(MathJaxCorrection.error.PARSE_ERROR, "Parse error");
	}
	
	// check for mathml parse error
	var nsResolver = function(prefix) {return MathJaxCorrection.namespaces.MATHML};
	var errorTextNode = mmlCorrectionDoc
		.evaluate("//mml:merror/mml:mtext/text()", mmlCorrectionDoc, nsResolver, XPathResult.FIRST_ORDERED_NODE_TYPE, null).singleNodeValue;
	if (errorTextNode != null) {
		var errorText = errorTextNode.nodeValue;
		throw new MathJaxCorrectionError(MathJaxCorrection.error.CORRECTION_FAILED,
						 "Correction failed: " + errorText);
	}
	
	// transform correction namespace to source namespace
	if (mmlCorrectionDoc.documentElement.namespaceURI === this.sourceDoc.documentElement.namespaceURI) {
		this.correctionDoc = mmlCorrectionDoc;
	} else {
		var nnDoc = this.sourceScript.ownerDocument.implementation.createDocument("", "", null);
		this.correctionDoc = MathJaxCorrection.inNsToOutNs(mmlCorrectionDoc, nnDoc,
								   this.sourceDoc.documentElement.namespaceURI);
	}

	// for non-MathML input
	if (this.correctionTopNode == undefined) {
		if (this.selectedNode.nodeName == "math") {
			this.correctionTopNode = this.correctionDoc.documentElement;
		} else {
			// MathJax wraps the formula in
			// <math xmlns="http://www.w3.org/1998/Math/MathML">
			this.correctionTopNode = this.correctionDoc.documentElement.firstChild;
		}
	}
}

/**
 * Parse and fix the correction MathML
 * @access private
 * @method
 * @param correctionText {string} the text of the correction
 * @returns {string} the fixed text of the correction
 * @throws {MathJaxCorrectionError} MathJaxCorrection.error.PARSE_ERROR
 */
MathJaxCorrection.prototype._parseAndFixMathml = function(correctionText) {
	var correctionDoc = new DOMParser().parseFromString(correctionText, "text/xml");
	var correctionRootNode = correctionDoc.documentElement;
	if (correctionRootNode.nodeName == "parsererror") {
		throw new MathJaxCorrectionError(MathJaxCorrection.error.PARSE_ERROR, "Parse error");
	}
	if (correctionRootNode.nodeName != "math") {
		var mathRootNode = correctionDoc.createElementNS(correctionRootNode.namespaceURI, "math");
		correctionDoc.importNode(mathRootNode);
		correctionDoc.replaceChild(mathRootNode, correctionRootNode);
		mathRootNode.appendChild(correctionRootNode);
		var serializer = new XMLSerializer();
		correctionText = serializer.serializeToString(correctionDoc);
	}
	return correctionText;
}

/**
 * Convert non-MathML correction to MathML;
 * inserts the correction into a temporary span, asks MathJax to render the correction, and gets the MathML formula from MathJax
 * @static
 * @method
 * @access public
 * @param correctionText {string} the text of the correction
 * @param correctionType {MathJaxCorrection.MathJaxMimeTypes} the correction type, a value from MathJaxCorrection.MathJaxMimeTypes
 * @param parent {Element} the element to which the temporary span element can be appended
 * @returns {string} the text of the correction in MathML
 * @throws {MathJaxCorrectionError} MathJaxCorrection.error.CORRECTION_FAILED
 */
MathJaxCorrection.OtherToMathml = function(correctionText, correctionType, parent)
{
	var doc = parent.ownerDocument;
	var span = doc.createElement("span");
	var insScript = doc.createElement("script");
	insScript.setAttribute("type", correctionType);
	insScript.innerHTML = correctionText;
	span.appendChild(insScript);
	parent.appendChild(span);
	MathJax.Hub.Update(span);
	var jaxs = MathJax.Hub.getAllJax(span);
	var jax = jaxs[0];
	if (jax == undefined) {
		throw new MathJaxCorrectionError(MathJaxCorrection.error.CORRECTION_FAILED, "Correction failed");
	}
	var correctionMathml = jax.root.toMathML();
	parent.removeChild(span);
	return correctionMathml;
}

/**
 * Convert a DOM tree to an identical DOM tree in another namespace;
 * calls itself recursively
 * @method
 * @static
 * @access public
 * @param {Node} inNode the input node
 * @param {Node} outNode the node to which the new node is appended
 * @param {URI} outNamespaceURI the output namespace
 * @returns {Node} outNode
 */
MathJaxCorrection.inNsToOutNs = function(inNode, outNode, outNamespaceURI) {
	// out tree has no mixed content (MathML case)
	var outDoc = outNode.ownerDocument;
	if (outDoc == null) {
		outDoc = outNode;
	}
	for (var i = 0; i < inNode.childNodes.length; ++i) {
		var outChild;
		var inChild = inNode.childNodes[i];
		if (inChild.nodeType == Node.ELEMENT_NODE) {
			outChild = outDoc.createElementNS(outNamespaceURI, inChild.nodeName);
		} else if (inChild.nodeType == Node.TEXT_NODE
				   && inChild.parentNode.childElementCount != 0) {
			// artificial text nodes in MathML non-leaf elements
			continue;
		} else {
			outChild = outDoc.importNode(inChild);
		}
		outNode.appendChild(outChild);
		if (outChild.nodeType == Node.ELEMENT_NODE) {
			MathJaxCorrection.inNsToOutNs(inChild, outChild, outNamespaceURI);
		}
	}
	return outNode;
}

/**
 * Remove the selection from the DOM of the original formula
 * @method
 * @access private
 */
MathJaxCorrection.prototype._removeSelected = function() {

	this.deletedDoc = this.sourceScript.ownerDocument.implementation.createDocument("", "", null);
	var parentDelNode;
	parentDelNode = this.deletedDoc;
	if (this.selectedNode.nodeName != "math") {
		var delNode = this.deletedDoc.createElement("math");
		parentDelNode.appendChild(delNode);
		parentDelNode = delNode;
		delNode = this.deletedDoc.createElement("mrow");
		parentDelNode.appendChild(delNode);
		parentDelNode = delNode;
	}
	var node = this.selectedNode;
	var nextSibling = this.selectedNode.nextElementSibling;
	this.selectedParentNode = this.selectedNode.parentNode
	for (var i = 0; i < this.selectionLength; ++i) {
		var delNode = this.deletedDoc.importNode(node, true);
		parentDelNode.appendChild(delNode);
		nextSibling = node.nextElementSibling;
		this.selectedParentNode.removeChild(node);
		node = nextSibling;
	}
	this.selectedNode = null; // removed from sourceDoc
	this.selectedNextSibling = nextSibling;
}

/**
 * Insert the correction into the DOM of the original formula
 * @method
 * @access private
 */
MathJaxCorrection.prototype._insertCorrection = function() {

	for (var i = 0; i < this.correctionTopNode.parentNode.childNodes.length; ++i) {
		var sourceNode = this.sourceDoc.importNode(this.correctionTopNode.parentNode.childNodes[i], true);
		if (this.selectedNextSibling != null) {
			this.selectedParentNode.insertBefore(sourceNode, this.selectedNextSibling);
		} else {
			this.selectedParentNode.appendChild(sourceNode);
		}
	}
}

