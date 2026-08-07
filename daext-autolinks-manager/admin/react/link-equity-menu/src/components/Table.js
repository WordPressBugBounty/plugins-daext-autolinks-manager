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
        const lastPageIndex = firstPageIndex + PageSize;
        return props.data.slice(firstPageIndex, lastPageIndex);
    }, [currentPage, props.data]);

    //Pagination - END ----------------------------------------------------------

    function handleDataIcon(columnName) {

        return props.formData.sortingColumn === columnName ? props.formData.sortingOrder : '';

    }

    function formatLinkEquityValue(linkEquity) {

        if (linkEquity > 1000000000000) {
            return (linkEquity / 1000000000000).toFixed(1) + 'T';
        } else if (linkEquity > 1000000000) {
            return (linkEquity / 1000000000).toFixed(1) + 'B';
        } else if (linkEquity > 1000000) {
            return (linkEquity / 1000000).toFixed(1) + 'M';
        } else if (linkEquity > 1000) {
            return (linkEquity / 1000).toFixed(1) + 'K';
        } else{
            return parseInt(linkEquity, 10).toFixed(0);
        }

    }

    return (

        <div className="daextam-data-table-container">

            <table className="daextam-react-table__daextam-data-table daextam-react-table__daextam-data-table-link-equity-menu">
                <thead>
                <tr>
                    <th>
                        <button
                            className={'daextam-react-table__daextam-sorting-button'}
                            onClick={props.handleSortingChanges}
                            value={'url'}
                            data-icon={handleDataIcon('url')}
                            title={__('The URL of the page.', 'daext-autolinks-manager')}
                        >{__('URL', 'daext-autolinks-manager')}</button>
                    </th>
                    <th>
                        <button
                            className={'daextam-react-table__daextam-sorting-button'}
                            onClick={props.handleSortingChanges}
                            value={'iil'}
                            data-icon={handleDataIcon('iil')}
                            title={__('The number of internal links pointing to the URL.', 'daext-autolinks-manager')}
                        >{__('Internal Links', 'daext-autolinks-manager')}</button>
                    </th>
                    <th>
                        <button
                            className={'daextam-react-table__daextam-sorting-button'}
                            onClick={props.handleSortingChanges}
                            value={'link_equity'}
                            data-icon={handleDataIcon('link_equity')}
                            title={__('The amount of link equity that the URL has, based on the number and quality of internal links pointing to it.', 'daext-autolinks-manager')}
                        >{__('Link Equity', 'daext-autolinks-manager')}</button>
                    </th>

                </tr>
                </thead>
                <tbody>

                {currentTableData.map((row) => (
                    <tr key={row.id}>
                        <td>
                            <div className={'daextam-react-table__post-cell-container'}>
                                <a href={row.url}>{row.url}</a>
                                <a href={row.url} target={'_blank'}
                                   className={'daextam-react-table__icon-link'}></a>
                            </div>
                        </td>
                        <td>{row.iil}</td>
                        <td>
                            <div className={'link-equity-relative-wrapper'}>
                                <div className="link-equity-relative-container">
                                    <div className="link-equity-relative" style={{width: row.link_equity_relative + '%'}}></div>
                                </div>
                                <div className={'link-equity-value'}>{formatLinkEquityValue(row.link_equity)}</div>
                            </div>
                        </td>
                        <td>
                            <div className={'button-actions-container'}>
                                <button
                                    className={'small-button'}
                                    onClick={() => props.urlDetailsViewHandler(row.id, row.url)}
                                >Details View
                                </button>
                            </div>
                        </td>
                    </tr>
                ))}

                </tbody>
            </table>

            {props.data.length === 0 && <div
                className="daextam-no-data-found">{__('We couldn\'t find any results matching your filters. Try adjusting your criteria.', 'daext-autolinks-manager')}</div>}
            {props.data.length > 0 &&
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
            }

        </div>

    );

};

export default Chart;
