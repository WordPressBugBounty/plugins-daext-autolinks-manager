const { SelectControl } = wp.components;
const { useSelect, useDispatch } = wp.data;
const { PluginDocumentSettingPanel } = wp.editor;
const { __ } = wp.i18n;

const META_KEY = '_daextam_enable_autolinks';

const Sidebar = () => {

    /*
     * Read the post meta with a subscription to the "core/editor" store.
     *
     * Note that the post meta should not be read only once when the component is mounted (e.g. with
     * "componentDidMount"), because at that time the post entity record might not be available yet, and in that
     * situation "getEditedPostAttribute( 'meta' )" returns "undefined".
     */
    const meta = useSelect(
        (select) => select('core/editor').getEditedPostAttribute('meta'),
        []
    );

    const { editPost } = useDispatch('core/editor');

    // Do not render anything if the user does not have the required capability.
    if (parseInt(window.DAEXTAM_PARAMETERS.user_has_interlinks_options_mb_required_capability, 10) !== 1) {
        return null;
    }

    // Do not render anything if this editor tool is not enabled in this post type.
    if (parseInt(window.DAEXTAM_PARAMETERS.interlinks_options_is_active_in_post_type, 10) !== 1) {
        return null;
    }

    // Fall back to the default value defined in the plugin options when the meta is empty or not available yet.
    const metaValue = meta ? meta[META_KEY] : undefined;
    const enableAutolinks =
        metaValue === '' || metaValue === undefined || metaValue === null
            ? window.DAEXTAM_PARAMETERS.advanced_enable_autolinks
            : metaValue;

    return (
        <PluginDocumentSettingPanel
            name="daextam-automatic-links-options"
            title={__('Automatic Links', 'daext-autolinks-manager')}
        >

            <SelectControl
                label={__('Enable', 'daext-autolinks-manager')}
                help={__('Automatically add links based on the configured keywords.', 'daext-autolinks-manager')}
                value={enableAutolinks}
                options={[
                    { label: __('No', 'daext-autolinks-manager'), value: '0' },
                    { label: __('Yes', 'daext-autolinks-manager'), value: '1' },
                ]}
                onChange={(value) => {
                    editPost({
                        meta: {
                            [META_KEY]: value,
                        },
                    });
                }}
                __nextHasNoMarginBottom={true}
                __next40pxDefaultSize={true}
            />
        </PluginDocumentSettingPanel>
    );
};

export default Sidebar;
