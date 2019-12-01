import React from 'react';
import clsx from 'clsx';
import { makeStyles, useTheme } from '@material-ui/core/styles';
import { useMediaQuery, useState, TextField, Container } from '@material-ui/core';
import MonacoEditor from '@monaco-editor/react';
import { FillSpinner as Loader } from "react-spinners-kit";

const useStyles = makeStyles({
    root: {
        background: 'linear-gradient(45deg, #FE6B8B 30%, #FF8E53 90%)',
        border: 0,
        borderRadius: 3,
        boxShadow: '0 3px 5px 2px rgba(255, 105, 135, .3)',
        color: 'white',
        height: '100%',
        width: '100%',
        padding: '0 30px',
    },
});

function Preview() {
    const classes = useStyles();
    const theme = useTheme();
    return <iframe className={classes.root} src="demo.html" />;
}

export default Preview;
