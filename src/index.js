import React from 'react';
import ReactDOM from 'react-dom';
import 'bootstrap/dist/css/bootstrap.min.css';
//import './stylish-portfolio/css/stylish-portfolio.min.css';
//import './sb-admin-2/css/sb-admin-2.min.css';

import './index.css';
import App from './App';
import * as serviceWorker from './serviceWorker';

import { library } from '@fortawesome/fontawesome-svg-core';
import { faEdit, faCogs, faFileAlt, faPaintBrush, faCode, faPalette } from '@fortawesome/free-solid-svg-icons';
library.add(faEdit, faCogs, faFileAlt, faPaintBrush, faCode, faPalette);

ReactDOM.render(<App />, document.getElementById('root'));

// If you want your app to work offline and load faster, you can change
// unregister() to register() below. Note this comes with some pitfalls.
// Learn more about service workers: https://bit.ly/CRA-PWA
serviceWorker.unregister();
