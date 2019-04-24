import React from 'react';
import {
	Row,
	Col,
	Form
} from 'react-bootstrap';
import MonacoEditor from 'react-monaco-editor';

import './write.css';

export default class Write extends React.Component {
	constructor(props) {
		super(props);
		this.state = {
			code: 'Let\'s write!',
		};
	}
	editorDidMount(editor, monaco) {
		//console.log('editorDidMount', editor);
		editor.focus();
	}
	onChange(newValue, e) {
		console.log('onChange', newValue, e);
	}
	render() {
		const code = this.state.code;
		const options = {
			lineNumbers: false,
			renderLineHighlight: 'none',
			automaticLayout: true
		};
		return (
			<div>
				<Row>
					<Col>
						<Form.Control size="lg" type="text" className="title-input" placeholder="Post title" />
					</Col>
				</Row>
				<Row>
					<Col xs={2}>
						Titles
					</Col>
					<Col xs={10}>
						<MonacoEditor
							width="100%"
							height="300"
							language="markdown"
							theme="vs-light"
							value={code}
							options={options}
							onChange={this.onChange.bind(this)}
							editorDidMount={this.editorDidMount.bind(this)}
						/>
					</Col>
				</Row>
			</div>
		);
	}
}
