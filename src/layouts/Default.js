import React from 'react';
import PropTypes from 'prop-types';
import Split from 'react-split';
import { Container, Row, Col } from 'react-bootstrap';

import './Default.css';

import Sidebar from '../components/layout/Sidebar';

function onDrag() {
	console.log('DRAG');
	console.warn('Not implemented: call monacoEditor.layout()', 'https://github.com/Microsoft/monaco-editor/issues/28#issuecomment-228523529');
}

const DefaultLayout = ({ children, noNavbar, noFooter }) => (
	<Split sizes={[60, 40]} gutterSize={3} className="flex" onDrag={onDrag}>
		<Container fluid>
			<Row>
				<Sidebar />
				<Col
					className="main-content p-0"
					tag="main"
				>
					{children}
				</Col>
			</Row>
		</Container>
		<div>NOTHING</div>
	</Split>
);

DefaultLayout.propTypes = {
	/**
	 * Whether to display the navbar, or not.
	 */
	noNavbar: PropTypes.bool,
	/**
	 * Whether to display the footer, or not.
	 */
	noFooter: PropTypes.bool
};

DefaultLayout.defaultProps = {
	noNavbar: false,
	noFooter: false
};

export default DefaultLayout;
