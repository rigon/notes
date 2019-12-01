import React from 'react';
import clsx from 'clsx';
import { makeStyles, useTheme } from '@material-ui/core/styles';

import {
  Drawer,
  AppBar,
  Toolbar,
  List,
  CssBaseline,
  Typography,
  Divider,
  IconButton,
  ListItem,
  ListItemIcon,
  ListItemText,
} from '@material-ui/core';

import {
  Dashboard as DashboardIcon,
  AccountCircle as ProfileIcon,

  Menu as MenuIcon,
  ChevronLeft as ChevronLeftIcon,
  ChevronRight as ChevronRightIcon,

  TextFields as WriteIcon,
  Attachment as FilesIcon,
  Style as StylesIcon,
  Code as ScriptIcon,
  Palette as ThemesIcon
} from '@material-ui/icons';

import { Profile, SidebarNav, UpgradePlan } from './components';

const drawerWidth = 200;

const useStyles = makeStyles(theme => ({
  root: {
    display: 'flex',
  },
  appBar: {
    zIndex: theme.zIndex.drawer + 1,
    transition: theme.transitions.create(['width', 'margin'], {
      easing: theme.transitions.easing.sharp,
      duration: theme.transitions.duration.leavingScreen,
    }),
  },
  drawer: {
    width: drawerWidth,
    flexShrink: 0,
    whiteSpace: 'nowrap',
  },
  drawerOpen: {
    width: drawerWidth,
    transition: theme.transitions.create('width', {
      easing: theme.transitions.easing.sharp,
      duration: theme.transitions.duration.enteringScreen,
    }),
  },
  drawerClose: {
    transition: theme.transitions.create('width', {
      easing: theme.transitions.easing.sharp,
      duration: theme.transitions.duration.leavingScreen,
    }),
    overflowX: 'hidden',
    width: theme.spacing(7) + 1,
    [theme.breakpoints.up('sm')]: {
      width: theme.spacing(9) + 1,
    },
  },
  toolbar: {
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'flex-end',
    padding: theme.spacing(0, 1),
    ...theme.mixins.toolbar,
  },
  content: {
    flexGrow: 1,
    padding: theme.spacing(3),
  },
}));

export default function MiniDrawer() {
  const classes = useStyles();
  const theme = useTheme();
  const [open, setOpen] = React.useState(false);

  const chevronDirection = open ^ (theme.direction === 'rtl');

  const handleDrawerToggle = () => {
    setOpen(!open);
  }

  return (
    <div className={classes.root}>
      <Drawer
        variant="permanent"
        className={clsx(classes.drawer, {
          [classes.drawerOpen]: open,
          [classes.drawerClose]: !open,
        })}
        classes={{
          paper: clsx({
            [classes.drawerOpen]: open,
            [classes.drawerClose]: !open,
          }),
        }}
        open={open}
      >

        <List>
          <ListItem button key="dashboard" onClick={handleDrawerToggle} aria-label="toggle drawer">
            <ListItemIcon><MenuIcon /></ListItemIcon>
            <ListItemText primary="" />
          </ListItem>
        </List>

        <Profile />
        <List>
          <ListItem button key="dashboard">
            <ListItemIcon><DashboardIcon /></ListItemIcon>
            <ListItemText primary="Dashboard" />
          </ListItem>
          <ListItem button key="profile">
            <ListItemIcon><ProfileIcon /></ListItemIcon>
            <ListItemText primary="Profile" />
          </ListItem>
        </List>
        <Divider />

        <List>
          <ListItem button key="write">
            <ListItemIcon><WriteIcon /></ListItemIcon>
            <ListItemText primary="Write" />
          </ListItem>
          <ListItem button key="files">
            <ListItemIcon><FilesIcon /></ListItemIcon>
            <ListItemText primary="Files" />
          </ListItem>
          <ListItem button key="styles">
            <ListItemIcon><StylesIcon /></ListItemIcon>
            <ListItemText primary="Styles" />
          </ListItem>
          <ListItem button key="script">
            <ListItemIcon><ScriptIcon /></ListItemIcon>
            <ListItemText primary="Scripts" />
          </ListItem>
          <ListItem button key="themes">
            <ListItemIcon><ThemesIcon /></ListItemIcon>
            <ListItemText primary="Themes" />
          </ListItem>
        </List>
        <Divider />

        <div className={classes.toolbar}>
          <IconButton onClick={handleDrawerToggle}>
            { chevronDirection ? <ChevronLeftIcon /> : <ChevronRightIcon /> }
          </IconButton>
        </div>
      </Drawer>
    </div>
  );
}
