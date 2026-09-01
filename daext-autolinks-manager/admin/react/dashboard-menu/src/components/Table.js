const useState = wp.element.useState;
import Pagination from '../../../shared-components/pagination/Pagination';

const useMemo = wp.element.useMemo;
const {__} = wp.i18n;

let PageSize = window.DAEXTAM_PARAMETERS.items_per_page;

const Chart = (props) => {

    //Pagination - START --------------------------------------------------------

    const [currentPage, setCurrentPage] = useState(1);

    const currentTableData = useMemo(() => {
        const firstPageIndex = (currentPage - 1) * PageSize;
        const lastPageIndex  = firstPageIndex + PageSize;
        return props.data.slice(firstPageIndex, lastPageIndex);
    }, [currentPage, props.data]);

    //Pagination - END ----------------------------------------------------------

    function handleDataIcon(columnName) {
        return props.formData.sortingColumn === columnName ? props.formData.sortingOrder : '';
    }

    function getColumns(view) {
        if (view === 'internal') {
            return [
                'post_title',
                'post_date',
                'post_type',
                'content_length',
                'manual_interlinks',
                'auto_interlinks',
                'iil',
                'optimization',
            ];
        }
        // Default: 'automatic'
        return [
            'post_title',
            'post_date',
            'post_type',
            'content_length',
            'auto_links',
        ];
    }

    function formatInternalLinksStatus(manualInterlinks, autoInterlinks, recommendedInterlinks) {
        const internalLinks = parseInt(manualInterlinks, 10) + parseInt(autoInterlinks, 10);
        return internalLinks + ' of ' + recommendedInterlinks;
    }

    function getInternalLinksStatusClass(optimization) {
        return parseInt(optimization, 10) === 1
            ? 'daextam-react-table__check-circle'
            : 'daextam-react-table__x-circle';
    }

    function getInternalLinksTooltip(manualInterlinks, autoInterlinks, recommendedInterlinks) {
        const internalLinks = parseInt(manualInterlinks, 10) + parseInt(autoInterlinks, 10);
        return internalLinks + ' internal links. Recommended: ' + recommendedInterlinks + '.';
    }

    const columnDefinitions = {

        post_title: {
            label:    __('Post', 'daext-autolinks-manager'),
            tooltip:  __('The title of the post or page.', 'daext-autolinks-manager'),
            sortable: true,
            render:   (row) => (
                <div className={'daextam-react-table__post-cell-container'}>
                    <a href={row.post_permalink}>{row.post_title}</a>
                    <a href={row.post_permalink} target={'_blank'} className={'daextam-react-table__icon-link'}></a>
                    <a href={row.post_edit_link} className={'daextam-react-table__icon-link'}></a>
                </div>
            ),
        },

        post_date: {
            label:    __('Date', 'daext-autolinks-manager'),
            tooltip:  __('The publication date of the post or page.', 'daext-autolinks-manager'),
            sortable: true,
            render:   (row) => row.formatted_post_date,
        },

        post_type: {
            label:    __('Type', 'daext-autolinks-manager'),
            tooltip:  __('The content type (e.g. post or page).', 'daext-autolinks-manager'),
            sortable: true,
            render:   (row) => row.post_type,
        },

        content_length: {
            label:    __('Length', 'daext-autolinks-manager'),
            tooltip:  __('The content length of the post in characters.', 'daext-autolinks-manager'),
            sortable: true,
            render:   (row) => row.content_length,
        },

        auto_links: {
            label:    __('Automatic Links', 'daext-autolinks-manager'),
            tooltip:  __('The number of links automatically generated based on auto link rules.', 'daext-autolinks-manager'),
            sortable: true,
            render:   (row) => row.auto_links,
        },

        manual_interlinks: {
            label:    __('Manual Internal Links', 'daext-autolinks-manager'),
            tooltip:  __('The number of internal links manually added to the content.', 'daext-autolinks-manager'),
            sortable: true,
            render:   (row) => row.manual_interlinks,
        },

        auto_interlinks: {
            label:    __('Auto Internal Links', 'daext-autolinks-manager'),
            tooltip:  __('The number of internal links automatically generated based on automatic link rules.', 'daext-autolinks-manager'),
            sortable: true,
            render:   (row) => row.auto_interlinks,
        },

        iil: {
            label:    __('Inbound Internal Links', 'daext-autolinks-manager'),
            tooltip:  __('The number of internal links pointing to this post from other content.', 'daext-autolinks-manager'),
            sortable: true,
            render:   (row) => row.iil,
        },

        optimization: {
            label:    __('Status', 'daext-autolinks-manager'),
            tooltip:  __('Indicates whether the number of internal links is within the recommended range based on content length.', 'daext-autolinks-manager'),
            sortable: true,
            render:   (row) => (
                <div
                    className={'daextam-react-table__post-cell-container'}
                    title={getInternalLinksTooltip(
                        row.manual_interlinks,
                        row.auto_interlinks,
                        row.recommended_interlinks
                    )}
                >
                    <div className={getInternalLinksStatusClass(row.optimization)}></div>
                    {formatInternalLinksStatus(
                        row.manual_interlinks,
                        row.auto_interlinks,
                        row.recommended_interlinks
                    )}
                </div>
            ),
        },

    };

    const activeColumns = getColumns(props.view);

    return (

        <div className="daextam-data-table-container">

            <table className="daextam-react-table__daextam-data-table daextam-react-table__daextam-data-table-dashboard-menu">
                <thead>
                <tr>
                    {activeColumns.map((column) => (
                        <th key={column}>
                            {columnDefinitions[column].sortable ? (
                                <button
                                    className={'daextam-react-table__daextam-sorting-button'}
                                    onClick={props.handleSortingChanges}
                                    value={column}
                                    data-icon={handleDataIcon(column)}
                                    title={columnDefinitions[column]?.tooltip || columnDefinitions[column].label}
                                >
                                    {columnDefinitions[column].label}
                                </button>
                            ) : (
                                <span title={columnDefinitions[column]?.tooltip || columnDefinitions[column].label}>
                                    {columnDefinitions[column].label}
                                </span>
                            )}
                        </th>
                    ))}
                </tr>
                </thead>
                <tbody>
                {currentTableData.map((row, rowIndex) => (
                    <tr key={row.statistic_id ?? row.id ?? rowIndex}>
                        {activeColumns.map((column) => (
                            <td key={column}>
                                {columnDefinitions[column].render(row)}
                            </td>
                        ))}
                    </tr>
                ))}
                </tbody>
            </table>

            {props.data.length === 0 && (
                <div className="daextam-no-data-found">
                    {__("We couldn't find any results matching your filters. Try adjusting your criteria.", 'daext-autolinks-manager')}
                </div>
            )}

            {props.data.length > 0 && (
                <div className="daextam-react-table__pagination-container">
                    <div className='daext-displaying-num'>{props.data.length + ' items'}</div>
                    <Pagination
                        className="pagination-bar"
                        currentPage={currentPage}
                        totalCount={props.data.length}
                        pageSize={PageSize}
                        onPageChange={page => setCurrentPage(page)}
                    />
                </div>
            )}

        </div>

    );

};

export default Chart;
