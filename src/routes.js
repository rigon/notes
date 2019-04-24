import React from "react";
import { Redirect } from "react-router-dom";

// Layout Types
import { DefaultLayout } from "./layouts";

// Route Views
import UserProfile from "./sections/UserProfile";
import Write from "./components/write";

export default [
  {
    path: "/",
    exact: true,
    layout: DefaultLayout,
    component: () => <Redirect to="/write" />,
    sidebar: false
  },
  {
    path: "/write",
    layout: DefaultLayout,
    component: Write,
    sidebar: true,
    title: "Write",
    icon: "edit"
  },
  {
    path: "/page",
    layout: DefaultLayout,
    component: Write,
    sidebar: true,
    title: "Page",
    icon: "cogs"
  },
  {
    path: "/files",
    layout: DefaultLayout,
    component: Write,
    sidebar: true,
    title: "Files",
    icon: "file-alt"
  },
  {
    path: "/styles",
    layout: DefaultLayout,
    component: Write,
    sidebar: true,
    title: "Styles",
    icon: "paint-brush"
  },
  {
    path: "/script",
    layout: DefaultLayout,
    component: Write,
    sidebar: true,
    title: "Script",
    icon: "code"
  },
  {
    path: "/themes",
    layout: DefaultLayout,
    component: Write,
    sidebar: true,
    title: "Themes",
    icon: "palette"
  },
  {
    path: "/user-profile",
    layout: DefaultLayout,
    component: UserProfile,
    sidebar: true,
    title: "User Profile"
  }
];
