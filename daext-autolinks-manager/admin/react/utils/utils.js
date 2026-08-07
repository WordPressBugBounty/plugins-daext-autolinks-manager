/**
 * Download a file from the provided string.
 *
 * @param content
 * @param fileNamePrefix
 */
export const downloadFileFromString = (content, fileNamePrefix) => {

    const blob = content;

    // Create a temporary URL to the blob.
    const url = window.URL.createObjectURL(new Blob([blob]));

    // Create a link element.
    const link = document.createElement('a');
    link.href = url;
    const fileName = fileNamePrefix + '-' + Date.now().toString().slice(0, 10) + '.csv';
    link.setAttribute('download', fileName); // Specify the filename

    // Append the link to the body.
    document.body.appendChild(link);

    // Trigger the click event on the link.
    link.click();

    // Cleanup.
    link.parentNode.removeChild(link);

}

/**
 * Shared react-select styles for the filter-column dropdowns used in the
 * Dashboard, Link Equity, Broken Links, and Clicks menus.
 *
 * The visual design follows the plugin's colour palette and matches the
 * react-select usage already found in the Options menu (SelectField.js).
 */
export const filterSelectStyles = {
    control: (provided, state) => ({
        ...provided,
        minHeight: 40,
        height: 40,
        borderColor: state.isFocused ? '#1e80d9' : '#d0d6dd',
        boxShadow: state.isFocused
            ? '0 0 0 1px #1e80d9'
            : '0px 1px 2px rgba(29, 40, 58, 0.10)',
        flexShrink: 0,
        cursor: 'pointer',
        fontSize: 13,
        borderRadius: 6,
        '&:hover': {
            borderColor: state.isFocused ? '#1e80d9' : '#d0d6dd',
        },
    }),
    valueContainer: (provided) => ({
        ...provided,
        height: 40,
        padding: '0 8px',
        flexWrap: 'nowrap',
    }),
    singleValue: (provided) => ({
        ...provided,
        color: '#344255',
        fontSize: 13,
        whiteSpace: 'nowrap',
    }),
    input: (provided) => ({
        ...provided,
        margin: 0,
        padding: 0,
        color: '#344255',
        fontSize: 13,
    }),
    indicatorsContainer: (provided) => ({
        ...provided,
        height: 40,
    }),
    indicatorSeparator: () => ({
        display: 'none',
    }),
    dropdownIndicator: (provided) => ({
        ...provided,
        padding: '0 8px',
        color: '#667485',
    }),
    option: (provided, state) => ({
        ...provided,
        backgroundColor: state.isSelected
            ? '#1e80d9'
            : state.isFocused
            ? '#f3f6fc'
            : '#ffffff',
        color: state.isSelected ? '#ffffff' : '#344255',
        fontSize: 13,
        cursor: 'pointer',
        padding: '6px 12px',
        ':active': {
            backgroundColor: '#145db4',
            color: '#ffffff',
        },
    }),
    menu: (provided) => ({
        ...provided,
        zIndex: 9999,
        borderRadius: 6,
        boxShadow: '0px 0px 0px 1px rgba(0,0,0,0.04), 0px 8px 23px rgba(0,0,0,0.12)',
        border: '1px solid #d0d6dd',
        overflow: 'hidden',
    }),
    menuList: (provided) => ({
        ...provided,
        padding: 4,
    }),
};

