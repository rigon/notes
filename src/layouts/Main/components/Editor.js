import React from 'react';
import { makeStyles, useTheme } from '@material-ui/core/styles';
import { IconButton, Grid } from '@material-ui/core';
import {
    Fullscreen as FullscreenIcon,
    OpenInBrowser as DetachPreviewIcon,
    Pageview as ShowPreviewIcon
} from '@material-ui/icons';

import MonacoEditor from '@monaco-editor/react';
import { FillSpinner as Loader } from "react-spinners-kit";

const useStyles = makeStyles({
    root: {
        fontSize: '1.7em',
        height: '60px',
        width: '100%',
        padding: '0 15px',
        border: 0,
    },
});

function Editor() {
    // const [theme, setTheme] = useState("light");
    // const [language, setLanguage] = useState("javascript");
    // const [isEditorReady, setIsEditorReady] = useState(false);

    
    const classes = useStyles();
    const theme = useTheme();


    const fullscreen = function () {
        var elem = document.getElementById("root");

        /* Note that we must include prefixes for different browsers, as they don't
           support the requestFullscreen method yet */
        if (elem.requestFullscreen) {
            elem.requestFullscreen();
        } else if (elem.mozRequestFullScreen) { /* Firefox */
            elem.mozRequestFullScreen();
        } else if (elem.webkitRequestFullscreen) { /* Chrome, Safari and Opera */
            elem.webkitRequestFullscreen();
        } else if (elem.msRequestFullscreen) { /* IE/Edge */
            elem.msRequestFullscreen();
        }
    }

    return (
        <>
            <Grid container alignItems="center">
                <Grid item xs>
                    <input className={classes.root} placeholder="Title" />
                </Grid>
                <Grid item>
                    <IconButton onClick={ fullscreen } aria-label="fullscreen">
                        <FullscreenIcon fontSize="small" />
                    </IconButton>
                    <IconButton aria-label="detach preview">
                        <DetachPreviewIcon fontSize="small" />
                    </IconButton>
                    <IconButton aria-label="show preview">
                        <ShowPreviewIcon fontSize="small" />
                    </IconButton>
                </Grid>
            </Grid>

            <MonacoEditor
                height="calc(100% - 60px)"
                theme="light"
                // language={language}
                loading={<Loader />}
                // value={examples[language]}
                // editorDidMount={handleEditorDidMount}
                // options={{ lineNumbers: "off" }}
            />
        </>
    );
}

export default Editor;
