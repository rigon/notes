import React from 'react';
import { BrowserRouter, Route } from 'react-router-dom';
import './App.css';
import routes from './routes';
import withTracker from './withTracker';


export default () => (
	<BrowserRouter basename={process.env.REACT_APP_BASENAME || ''}>
		<div>
			{routes.map((route, index) => {
				return (
					<Route
						key={index}
						path={route.path}
						exact={route.exact}
						component={withTracker(props => {
							return (
								<route.layout {...props}>
									<route.component {...props} />
								</route.layout>
							);
						})}
					/>
				);
			})}
		</div>
	</BrowserRouter>
);
