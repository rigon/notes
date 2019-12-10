import React from 'react';
import clsx from 'clsx';
import { makeStyles, useTheme } from '@material-ui/core/styles';
import { useMediaQuery, useState, TextField, Container } from '@material-ui/core';
import MonacoEditor from '@monaco-editor/react';
import { FillSpinner as Loader } from "react-spinners-kit";
import { NoEncryption } from '@material-ui/icons';

const useStyles = makeStyles({
    root: {
        background: 'linear-gradient(45deg, #FE6B8B 30%, #FF8E53)',
        border: 0,
        height: '100%',
        width: '100%',
    },
});

function Preview() {
    const classes = useStyles();
    const theme = useTheme();
    return <iframe className={classes.root} src="demo.html" />;
}

export default Preview;
