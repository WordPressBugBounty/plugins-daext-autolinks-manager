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

    function formatLinkEquityValue(linkEquity) {

        if (linkEquity == null || isNaN(linkEquity)) {
            return '';
        }

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

            <table className="daextam-react-table__daextam-data-table">
                <thead>
                <tr>
                    <th
                    title={__('The post where the link is located.', 'daext-autolinks-manager')}
                    >{__('Post', 'daext-autolinks-manager')}</th>
                    <th
                    title={__('The anchor text of the link.', 'daext-autolinks-manager')}
                    >{__('Anchor Text', 'daext-autolinks-manager')}</th>
                    <th
                    title={__('The amount of link equity that the link is passing to the target page.', 'daext-autolinks-manager')}
                    >{__('Link Equity', 'daext-autolinks-manager')}</th>
                </tr>
                </thead>
                <tbody>

                {currentTableData.map((row) => (
                    <tr key={row.id}>
                        <td>
                            <div className={'daextam-react-table__post-cell-container'}>
                                <a href={row.postPermalink}>
                                    {row.postTitle}
                                </a>
                                <a href={row.postPermalink} target={'_blank'}
                                   className={'daextam-react-table__icon-link'}></a>
                                <a href={row.postEditLink} className={'daextam-react-table__icon-link'}></a>
                            </div>
                        </td>
                        <td>{row.anchor}</td>
                        <td>
                            <div className={'link-equity-relative-wrapper'}>
                                <div className="link-equity-relative-container">
                                    <div className="link-equity-relative" style={{width: row.linkEquityVisual + '%'}}></div>
                                </div>
                                <div className={'link-equity-value'}>{formatLinkEquityValue(row.linkEquity)}</div>
                            </div>
                        </td>
                    </tr>
                ))}

                </tbody>
            </table>

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

        </div>

    );

};

export default Chart;
