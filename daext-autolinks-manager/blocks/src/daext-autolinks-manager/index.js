import './editor.css';

const {__} = wp.i18n;
const {registerPlugin} = wp.plugins;
const {PluginSidebar} = wp.editPost;
const {SelectControl} = wp.components;
const {withSelect, withDispatch} = wp.data;
const {Component} = wp.element;

class Daext_Autolinks_Manager_Pro extends Component {

  constructor(){

    super(...arguments);

    /**
     * If the '_daextam_enable_autolinks' meta of this post is not defined get its value from the plugin options from a
     * custom endpoint of the WordPress Rest API.
     */
    if(wp.data.select('core/editor').getEditedPostAttribute('meta')['_daextam_enable_autolinks'].length === 0){

      wp.apiFetch( { path: '/daext-autolinks-manager/v1/read-options', method: 'POST' } ).then(
          ( data ) => {

            wp.data.dispatch( 'core/editor' ).editPost(
                { meta: { _daextam_enable_autolinks: data.daextam_advanced_enable_autolinks } }
            );

          },
          ( err ) => {

            return err;

          }
      );

    }

  }

  render() {

    const MetaBlockField = function(props) {
      return (
          <SelectControl
              label={__('Enable Autolinks', 'daext-autolinks-manager')}
              value={props.metaFieldValue}
              options={[
                {value: '0', label: __('No', 'daext-autolinks-manager')},
                {value: '1', label: __('Yes', 'daext-autolinks-manager')},
              ]}
              onChange={function(content) {
                props.setMetaFieldValue(content);
              }}
          >
          </SelectControl>
      );
    };

    const MetaBlockFieldWithData = withSelect(function(select) {
      return {
        metaFieldValue: select('core/editor').getEditedPostAttribute('meta')
            ['_daextam_enable_autolinks'],
      };
    })(MetaBlockField);

    const MetaBlockFieldWithDataAndActions = withDispatch(
        function(dispatch) {
          return {
            setMetaFieldValue: function(value) {
              dispatch('core/editor').editPost(
                  {meta: {_daextam_enable_autolinks: value}},
              );
            },
          };
        },
    )(MetaBlockFieldWithData);


      const icon = (
          <svg xmlns="http://www.w3.org/2000/svg" version="1.1" viewBox="0 0 256 256">
              <g id="Layer_1" data-name="Layer 1">
                  <path className="cls-1"
                        d="M128,16c29.92,0,58.04,11.65,79.2,32.8,21.15,21.15,32.8,49.28,32.8,79.2s-11.65,58.04-32.8,79.2c-21.15,21.15-49.28,32.8-79.2,32.8s-58.04-11.65-79.2-32.8c-21.15-21.15-32.8-49.28-32.8-79.2s11.65-58.04,32.8-79.2c21.15-21.15,49.28-32.8,79.2-32.8M128,0C57.31,0,0,57.31,0,128s57.31,128,128,128,128-57.31,128-128S198.69,0,128,0h0Z"/>
              </g>
              <g id="Layer_2" data-name="Layer 2">
                  <path className="cls-1"
                        d="M128,56c-17.67,0-32,14.33-32,32v8h16v-8c0-8.82,7.18-16,16-16s16,7.18,16,16v32c0,8.82-7.18,16-16,16v16c17.67,0,32-14.33,32-32v-32c0-17.67-14.33-32-32-32Z"/>
                  <path className="cls-1"
                        d="M144,160v8c0,8.82-7.18,16-16,16s-16-7.18-16-16v-32c0-8.82,7.18-16,16-16v-16c-17.67,0-32,14.33-32,32v32c0,17.67,14.33,32,32,32s32-14.33,32-32v-8h-16Z"/>
              </g>
          </svg>
      );

    return (
        <PluginSidebar
            name='daext-autolinks-manager-sidebar'
            icon={icon}
            title={__('Autolinks Manager', 'daext-autolinks-manager')}
        >
          <div
              className='daext-autolinks-manager-sidebar-content'
          >
            <MetaBlockFieldWithDataAndActions></MetaBlockFieldWithDataAndActions>
          </div>
        </PluginSidebar>
    );

  }

}

registerPlugin('daextam-autolinks-manager', {
  render: Daext_Autolinks_Manager_Pro,
});