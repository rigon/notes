import React from "react";
import PropTypes from "prop-types";
import { LinkContainer } from "react-router-bootstrap";
import { Nav } from "react-bootstrap";
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';

const SidebarNavItem = ({key, item}) => { console.log(item); return (
  <Nav.Item>
    <LinkContainer to={item.path}>
      <Nav.Link eventKey={key}>
        <FontAwesomeIcon icon={item.icon} size="lg" className="fa-icon" /> {item.title}
      </Nav.Link>
    </LinkContainer>
  </Nav.Item>
)};

SidebarNavItem.propTypes = {
  /**
   * The item object.
   */
  item: PropTypes.object
};

export default SidebarNavItem;
