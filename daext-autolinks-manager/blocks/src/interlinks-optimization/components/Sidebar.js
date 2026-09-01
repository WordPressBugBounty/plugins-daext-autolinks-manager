import getPostId from '../../shared/get-post-id';

const { Button } = wp.components;
const { PluginDocumentSettingPanel } = wp.editor;
const { useState, useEffect } = wp.element;
const { __ } = wp.i18n;
const apiFetch = wp.apiFetch;

const Sidebar = () => {

  const [optimizationData, setOptimizationData] = useState(null);

    // Fetch interlinks optimization data when the component mounts and on post save.
    useEffect(() => {

        const fetchData = () => {

            const postId = getPostId();

            // Do not perform the request if the post ID is not available.
            if (!postId) {
                return;
            }

            wp.apiFetch({
                path: '/daext-autolinks-manager/v1/generate-interlinks-optimization',
                method: 'POST',
                data: { id: postId },
            })
                .then((response) => {
                    setOptimizationData(response);
                })
                .catch((error) => {
                    console.error('Error fetching interlinks optimization data:', error);
                });
        };

        // Initial fetch
        fetchData();

        let wasSaving = false;

        const unsubscribe = wp.data.subscribe(() => {
            const isSaving = wp.data.select('core/editor').isSavingPost();
            const isAutosaving = wp.data.select('core/editor').isAutosavingPost();

            // Detect when a manual save completes
            if (wasSaving && !isSaving && !isAutosaving) {
                fetchData(); // Re-fetch optimization data
            }

            wasSaving = isSaving;
        });

        return () => {
            unsubscribe();
        };
    }, []);

  // Do not render anything if the user does not have the required capability.
  if (parseInt(window.DAEXTAM_PARAMETERS.user_has_interlinks_optimization_mb_required_capability, 10) !== 1) {
    return null;
  }

  // Do not render anything if this editor tool is not enabled in this post type.
  if (parseInt(window.DAEXTAM_PARAMETERS.interlinks_optimization_is_active_in_post_type, 10) !== 1) {
    return null;
  }

  return (
      <PluginDocumentSettingPanel
          name="daextam-interlinks-optimization"
          title={__('Internal Links Optimization', 'daext-autolinks-manager')}
      >
        <div className="daextam-container">
          <div className="daextam-meta-message">
            {optimizationData ? (() => {
              const totalNumberOfInterlinks = optimizationData['total_number_of_interlinks'];
              const numberOfManualInterlinks = optimizationData['number_of_manual_interlinks'];
              const numberOfAutoInterlinks = optimizationData['number_of_autolinks'];
              const suggestedMin = optimizationData['suggested_min_number_of_interlinks'];
              const suggestedMax = optimizationData['suggested_max_number_of_interlinks'];

              return totalNumberOfInterlinks >= suggestedMin && totalNumberOfInterlinks <= suggestedMax ? (
                  <p>{__('The number of internal links in this post is within the recommended range.', 'daext-autolinks-manager')}</p>
              ) : (
                  <>
                    <p>
                      {__('This post currently contains', 'daext-autolinks-manager')}&nbsp;
                      {totalNumberOfInterlinks}&nbsp;
                      {totalNumberOfInterlinks === 1
                          ? __('internal link', 'daext-autolinks-manager')
                          : __('internal links', 'daext-autolinks-manager')}
                      . ({numberOfManualInterlinks}&nbsp;
                      {numberOfManualInterlinks === 1
                          ? __('manual internal link', 'daext-autolinks-manager')
                          : __('manual internal links', 'daext-autolinks-manager')}
                      &nbsp;
                      {__('and', 'daext-autolinks-manager')}&nbsp;
                      {numberOfAutoInterlinks}&nbsp;
                      {numberOfAutoInterlinks === 1
                          ? __('auto internal link', 'daext-autolinks-manager')
                          : __('auto internal links', 'daext-autolinks-manager')}
                      )
                    </p>

                    {suggestedMin === suggestedMax ? (
                        <p>
                          {__('Based on the content length and your settings, the recommended number is', 'daext-autolinks-manager')}&nbsp;
                          {suggestedMin}.
                        </p>
                    ) : (
                        <p>
                          {__('Based on the content length and your settings, the recommended number is between', 'daext-autolinks-manager')}&nbsp;
                          {suggestedMin}&nbsp;
                          {__('and', 'daext-autolinks-manager')}&nbsp;
                          {suggestedMax}.
                        </p>
                    )}
                  </>
              );
            })() : (
                <p></p>
            )}
          </div>
        </div>
      </PluginDocumentSettingPanel>
  );
};

export default Sidebar;
