const useState = wp.element.useState;
import Pagination from '../../../shared-components/pagination/Pagination';

const useMemo = wp.element.useMemo;
const {__} = wp.i18n;

let PageSize = 10;

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

    return (

        <div className="daextam-data-table-container">

            <table className="daextam-react-table__daextam-data-table daextam-react-table__daextam-data-table-dashboard-menu">
                <thead>
                <tr>
                    <th>
                        <button
                            className={'daextam-react-table__daextam-sorting-button'}
                            onClick={props.handleSortingChanges}
                            value={'post_id'}
                            data-icon={handleDataIcon('post_id')}
                        >{__('Post', 'daext-autolinks-manager')}</button>
                    </th>
                    <th>
                        <button
                            className={'daextam-react-table__daextam-sorting-button'}
                            onClick={props.handleSortingChanges}
                            value={'post_date'}
                            data-icon={handleDataIcon('post_date')}
                        >{__('Date', 'daext-autolinks-manager')}</button>
                    </th>
                    <th>
                        <button
                            className={'daextam-react-table__daextam-sorting-button'}
                            onClick={props.handleSortingChanges}
                            value={'post_type'}
                            data-icon={handleDataIcon('post_type')}
                        >{__('Type', 'daext-autolinks-manager')}</button>
                    </th>
                    <th>
                        <button
                            className={'daextam-react-table__daextam-sorting-button'}
                            onClick={props.handleSortingChanges}
                            value={'content_length'}
                            data-icon={handleDataIcon('content_length')}
                        >{__('Length', 'daext-autolinks-manager')}</button>
                    </th>
                    <th>
                        <button
                            className={'daextam-react-table__daextam-sorting-button'}
                            onClick={props.handleSortingChanges}
                            value={'auto_links'}
                            data-icon={handleDataIcon('auto_links')}
                        >{__('Automatic Links', 'daext-autolinks-manager')}</button>
                    </th>
                </tr>
                </thead>
                <tbody>

                {currentTableData.map((row) => (
                    <tr key={row.statistic_id}>
                        <td>
                            <div className={'daextam-react-table__post-cell-container'}>
                                <a href={row.post_permalink}>
                                    {row.post_title}
                                </a>
                                <a href={row.post_permalink} target={'_blank'}
                                   className={'daextam-react-table__icon-link'}></a>
                                <a href={row.post_edit_link} className={'daextam-react-table__icon-link'}></a>
                            </div>
                        </td>
                        <td>{row.formatted_post_date}</td>
                        <td>{row.post_type}</td>
                        <td>{row.content_length}</td>
                        <td>{row.auto_links}</td>
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
