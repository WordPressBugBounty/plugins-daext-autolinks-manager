const {registerPlugin} = wp.plugins;
import render from './components/Sidebar';

registerPlugin(
    'daextam-automatic-links-options',
    {
      icon: false,
      render,
    },
);