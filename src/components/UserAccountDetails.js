import React from 'react';
import PropTypes from 'prop-types';
import {
	Card,
	ListGroup,
	ListGroupItem,
	Row,
	Col,
	Form,
	FormControl,
	Button
} from 'react-bootstrap';

const UserAccountDetails = ({ title }) => (
	<Card small className="mb-4">
		<Card.Header className="border-bottom">
			<h6 className="m-0">{title}</h6>
		</Card.Header>
		<ListGroup flush>
			<ListGroupItem className="p-3">
				<Row>
					<Col>
						<Form>
							<Row form>
								{/* First Name */}
								<Col md="6" className="form-group">
									<label htmlFor="feFirstName">First Name</label>
									<FormControl
										id="feFirstName"
										placeholder="First Name"
										value="Sierra"
										onChange={() => {}}
									/>
								</Col>
								{/* Last Name */}
								<Col md="6" className="form-group">
									<label htmlFor="feLastName">Last Name</label>
									<FormControl
										id="feLastName"
										placeholder="Last Name"
										value="Brooks"
										onChange={() => {}}
									/>
								</Col>
							</Row>
							<Row form>
								{/* Email */}
								<Col md="6" className="form-group">
									<label htmlFor="feEmail">Email</label>
									<FormControl
										as="email"
										id="feEmail"
										placeholder="Email Address"
										value="sierra@example.com"
										onChange={() => {}}
										autoComplete="email"
									/>
								</Col>
								{/* Password */}
								<Col md="6" className="form-group">
									<label htmlFor="fePassword">Password</label>
									<FormControl
										as="password"
										id="fePassword"
										placeholder="Password"
										value="EX@MPL#P@$$w0RD"
										onChange={() => {}}
										autoComplete="current-password"
									/>
								</Col>
							</Row>
							<Form.Group>
								<label htmlFor="feAddress">Address</label>
								<FormControl
									id="feAddress"
									placeholder="Address"
									value="1234 Main St."
									onChange={() => {}}
								/>
							</Form.Group>
							<Row form>
								{/* City */}
								<Col md="6" className="form-group">
									<label htmlFor="feCity">City</label>
									<FormControl
										id="feCity"
										placeholder="City"
										onChange={() => {}}
									/>
								</Col>
								{/* State */}
								<Col md="4" className="form-group">
									<label htmlFor="feInputState">State</label>
									<FormControl as="select" id="feInputState">
										<option>Choose...</option>
										<option>...</option>
									</FormControl>
								</Col>
								{/* Zip Code */}
								<Col md="2" className="form-group">
									<label htmlFor="feZipCode">Zip</label>
									<FormControl
										id="feZipCode"
										placeholder="Zip"
										onChange={() => {}}
									/>
								</Col>
							</Row>
							<Row form>
								{/* Description */}
								<Col md="12" className="form-group">
									<label htmlFor="feDescription">Description</label>
									<FormControl type="textarea" id="feDescription" rows="5" />
								</Col>
							</Row>
							<Button theme="accent">Update Account</Button>
						</Form>
					</Col>
				</Row>
			</ListGroupItem>
		</ListGroup>
	</Card>
);

UserAccountDetails.propTypes = {
	/**
	 * The component's title.
	 */
	title: PropTypes.string
};

UserAccountDetails.defaultProps = {
	title: 'Account Details'
};

export default UserAccountDetails;
