import React, { useState } from 'react';
import PropTypes from 'prop-types';
import clsx from 'clsx';
import { makeStyles, useTheme } from '@material-ui/styles';
import { useMediaQuery } from '@material-ui/core';
import SplitPane from 'react-split-pane';
import Typography from '@material-ui/core/Typography';
import Container from '@material-ui/core/Container';

import { Sidebar, Topbar, Footer, Editor, Preview } from './components';

import './split.css';

const useStyles = makeStyles(theme => ({
  root: {
    display: 'flex',
    height: '100%',
    // [theme.breakpoints.up('sm')]: {
    //   paddingTop: 64
    // }
  },
  shiftContent: {
    // paddingLeft: 73
  },
  content: {
    height: '100%'
  }
}));

const Main = props => {
  const { children } = props;

  const classes = useStyles();
  const theme = useTheme();
  const isDesktop = useMediaQuery(theme.breakpoints.up('lg'), {
    defaultMatches: true
  });

  const [openSidebar, setOpenSidebar] = useState(false);

  const handleSidebarOpen = () => {
    setOpenSidebar(true);
  };

  const handleSidebarClose = () => {
    setOpenSidebar(false);
  };

  const showPreviewOverlay = () => {
    document.getElementById('overlay').style.display = 'block';
  }
  const hidePreviewOverlay = () => {
    document.getElementById('overlay').style.display = 'none';
  }

  const shouldOpenSidebar = isDesktop ? true : openSidebar;

  return (

    <div
      className={clsx({
        [classes.root]: true,
        [classes.shiftContent]: isDesktop
      })}
    >
      <Sidebar
        onClose={handleSidebarClose}
        onOpen={handleSidebarOpen}
        open={shouldOpenSidebar}
        variant={isDesktop ? 'persistent' : 'temporary'}
      />
        <SplitPane
          split="vertical"
          minSize={500}
          onDragStarted={showPreviewOverlay}
          onDragFinished={hidePreviewOverlay}>
            <Editor />
            <div style={{ height: "100%", width: "100%" }}>
              <div id='overlay' style={{ height: "100%", width: "100%", position: "absolute", display: "none" }}></div>
              <Preview />
            </div>
        </SplitPane>
      </div>
  );
};

Main.propTypes = {
  children: PropTypes.node
};

export default Main;
