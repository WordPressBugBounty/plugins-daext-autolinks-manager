/**
 * Returns the ID of the post currently edited in the block editor.
 *
 * The ID is retrieved from the "core/editor" store, and when the store is not ready yet (which can happen while the
 * components of the sidebar are mounted) the value of the "post_ID" hidden input available in the classic editor
 * screen is used as a fallback.
 *
 * @return {number|null} The post ID or null if the post ID is not available.
 */
export default function getPostId() {

    const editorStore = wp.data.select('core/editor');

    if (editorStore && typeof editorStore.getCurrentPostId === 'function') {
        const postId = editorStore.getCurrentPostId();
        if (postId) {
            return parseInt(postId, 10);
        }
    }

    const postIdInput = document.getElementById('post_ID');
    if (postIdInput && postIdInput.value) {
        return parseInt(postIdInput.value, 10);
    }

    return null;

}

