const { TextControl } = wp.components;
const { SelectControl } = wp.components;
const { dispatch, select } = wp.data;
const { PluginDocumentSettingPanel } = wp.editor;
const { Component } = wp.element;
const { __ } = wp.i18n;

export default class Sidebar extends Component {
    constructor(props) {
        super(...arguments);

        // The state is used only to rerender the component with setState
        this.state = {
            enableAutolinks: 'text',
        };
    }

    componentDidMount() {
        const meta = select('core/editor').getEditedPostAttribute('meta');
        let enableAutolinks = meta['_daextam_enable_autolinks'];

        if (enableAutolinks === '' || enableAutolinks === undefined) {
            enableAutolinks = window.DAEXTAM_PARAMETERS.advanced_enable_autolinks;
        }

        this.setState({
            enableAutolinks: enableAutolinks,
        });
    }

    render() {
        // Do not render anything if the user does not have the required capability.
        if (parseInt(window.DAEXTAM_PARAMETERS.user_has_interlinks_options_mb_required_capability, 10) !== 1) {
            return null;
        }

        // Do not render anything if this editor tool is not enabled in this post type.
        if (parseInt(window.DAEXTAM_PARAMETERS.interlinks_options_is_active_in_post_type, 10) !== 1) {
            return null;
        }

        return (
            <PluginDocumentSettingPanel
                name="daextam-automatic-links-options"
                title={__('Automatic Links', 'daext-autolinks-manager')}
            >

                <SelectControl
                    label={__('Enable', 'daext-autolinks-manager')}
                    help={__('Automatically add links based on the configured keywords.', 'daext-autolinks-manager')}
                    value={this.state.enableAutolinks}
                    options={[
                        { label: __('No', 'daext-autolinks-manager'), value: '0' },
                        { label: __('Yes', 'daext-autolinks-manager'), value: '1' },
                    ]}
                    onChange={(value) => {
                        dispatch('core/editor').editPost({
                            meta: {
                                '_daextam_enable_autolinks': value,
                            },
                        });

                        // Used to rerender the component
                        this.setState({
                            enableAutolinks: value,
                        });
                    }}
                    __nextHasNoMarginBottom={true}
                    __next40pxDefaultSize={true}
                />
            </PluginDocumentSettingPanel>
        );
    }
}