import React from "react";
import PropTypes from "prop-types";
import { Nav } from "react-bootstrap";

import routes from '../../../routes';
import './Sidebar.css';

import SidebarNavItem from "./SidebarNavItem";

import { Store } from "../../../flux";

class Sidebar extends React.Component {
  constructor(props) {
    super(props);

    this.state = {
      menuVisible: false,
      sidebarNavItems: Store.getSidebarItems()
    };

    this.onChange = this.onChange.bind(this);
  }

  componentWillMount() {
    Store.addChangeListener(this.onChange);
  }

  componentWillUnmount() {
    Store.removeChangeListener(this.onChange);
  }

  onChange() {
    this.setState({
      ...this.state,
      menuVisible: Store.getMenuState(),
      sidebarNavItems: Store.getSidebarItems()
    });
  }

  render() {
    return (
      <Nav className="d-none d-md-block sidebar">
        {routes.filter(route => route.sidebar).map(
          (item, index) => (<SidebarNavItem key={index} item={item} />)
        )}
      </Nav>
    );
  }
}

Sidebar.propTypes = {
  /**
   * Whether to hide the logo text, or not.
   */
  hideLogoText: PropTypes.bool
};

Sidebar.defaultProps = {
  hideLogoText: false
};

export default Sidebar;
