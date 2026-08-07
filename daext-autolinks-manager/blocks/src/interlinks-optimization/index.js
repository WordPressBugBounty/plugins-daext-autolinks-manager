const {registerPlugin} = wp.plugins;
import render from './components/Sidebar';

registerPlugin(
    'daextam-interlinks-optimization',
    {
      icon: false,
      render,
    },
);