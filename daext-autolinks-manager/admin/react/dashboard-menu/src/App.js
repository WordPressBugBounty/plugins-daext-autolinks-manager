import Table from './components/Table';
import {downloadFileFromString} from '../../utils/utils';
import RefreshIcon from '../../../assets/img/icons/refresh-cw-01.svg';
import LoadingScreen from "../../shared-components/LoadingScreen";

const useState = wp.element.useState;
const useEffect = wp.element.useEffect;

const {__} = wp.i18n;

let statisticsDataLastUpdate = window.DAEXTAM_PARAMETERS.statistics_data_last_update;
let statisticsDataUpdateFrequency = window.DAEXTAM_PARAMETERS.statistics_data_update_frequency;
let currentTime = window.DAEXTAM_PARAMETERS.current_time;

const App = () => {

    const [formData, setFormData] = useState(
        {
            optimizationStatus: 0,
            searchString: '',
            searchStringChanged: false,
            sortingColumn: 'post_date',
            sortingOrder: 'desc'
        }
    );

    const [dataAreLoading, setDataAreLoading] = useState(true);

    const [dataUpdateRequired, setDataUpdateRequired] = useState(false);

    const [tableData, setTableData] = useState([]);
    const [statistics, setStatistics] = useState({
        allPosts: 0,
        averageAl: 0
    });

    useEffect(() => {

        let automaticUpdate = false;

        if (statisticsDataLastUpdate === ''){

            /**
             * If the statistics data last update is empty, it means that the data has never been updated. In this
             * case, enable the automatic update.
             */

            automaticUpdate = true;

        }else{

            /**
             * If the statistics data last update is not empty, verify if the data needs to be updated based on the
             * update frequency set in the plugin settings.
             */

            // Convert the MySQL date string into a JavaScript Date object.
            let date = new Date(currentTime);

            switch (statisticsDataUpdateFrequency) {

                case 'hourly':

                    // Remove one hour from date.
                    date.setHours(date.getHours() - 1);

                    if (new Date(statisticsDataLastUpdate) < date) {
                        automaticUpdate = true;
                    }

                    break;
                case 'daily':

                    // remove one day from date.
                    date.setDate(date.getDate() - 1);

                    if (new Date(statisticsDataLastUpdate) < date) {
                        automaticUpdate = true;
                    }

                    break;
                case 'weekly':

                    // Remove one week from date.
                    date.setDate(date.getDate() - 7);

                    if (new Date(statisticsDataLastUpdate) < date) {
                        automaticUpdate = true;
                    }

                    break;
                case 'monthly':

                    // Remove one month from date.
                    date.setMonth(date.getMonth() - 1);

                    if (new Date(statisticsDataLastUpdate) < date) {
                        automaticUpdate = true;
                    }

                    break;

            }

        }

        /**
         * If an automatic data update is required, and it's not already set the dataUpdateRequired state to true, set it
         * to true and return. By changing the dataUpdateRequired state to true, useEffect will be triggered again and
         * this time with the dataUpdateRequired state set to true, and the data will be updated.
         */
        if(automaticUpdate && !dataUpdateRequired){

            statisticsDataLastUpdate = currentTime;
            setDataUpdateRequired(true);
            return;

        }

        setDataAreLoading(true);

        /**
         * Initialize the chart data with the data received from the REST API
         * endpoint provided by the plugin.
         */
        wp.apiFetch({
            path: '/daext-autolinks-manager/v1/statistics',
            method: 'POST',
            data: {
                search_string: formData.searchString,
                sorting_column: formData.sortingColumn,
                sorting_order: formData.sortingOrder,
                data_update_required: dataUpdateRequired
            }
        }).then(data => {

                // Set the table data with setTableData().
                setTableData(data.table);

                // Set the statistics.
                setStatistics({
                    allPosts: data.statistics.all_posts,
                    averageAl: data.statistics.average_al
                });

                if (dataUpdateRequired) {

                    // Set the dataUpdateRequired state to false.
                    setDataUpdateRequired(false);

                    // Set the form data to the initial state.
                    setFormData({
                        searchString: '',
                        searchStringChanged: false,
                        sortingColumn: 'post_date',
                        sortingOrder: 'desc'
                    });

                }

                setDataAreLoading(false);

            },
        );

    }, [
        formData.searchStringChanged,
        formData.sortingColumn,
        formData.sortingOrder,
        dataUpdateRequired
    ]);

    /**
     * Function to handle key press events.
     *
     * @param event
     */
    function handleKeyUp(event) {

        // Check if Enter key is pressed (key code 13).
        if (event.key === 'Enter') {
            event.preventDefault(); // Prevent form submission.
            document.getElementById('daextam-search-button').click(); // Simulate click on search button.
        }

    }

    /**
     * Handle sorting changes.
     * @param e
     */
    function handleSortingChanges(e) {

        /**
         * Check if the sorting column is the same as the previous one.
         * If it is, change the sorting order.
         * If it is not, change the sorting column and set the sorting order to 'asc'.
         */
        let sortingOrder = formData.sortingOrder;
        if (formData.sortingColumn === e.target.value) {
            sortingOrder = formData.sortingOrder === 'asc' ? 'desc' : 'asc';
        }

        setFormData({
            ...formData,
            sortingColumn: e.target.value,
            sortingOrder: sortingOrder
        })

    }

    /**
     * Used to toggle the dataUpdateRequired value.
     * @param e
     */
    function handleDataUpdateRequired(e) {
        setDataUpdateRequired(prevDataUpdateRequired => {
            return !prevDataUpdateRequired;
        });
    }

    /**
     * Download the file with the CSV data.
     */
    function downloadExportFile() {

        wp.apiFetch({
            path: '/daext-autolinks-manager/v1/dashboard-menu-export-csv',
            method: 'POST'
        }).then(response => {

                downloadFileFromString(response.csv_content, 'dashboard');

            },
        );

    }

    return (

        <>

            <React.StrictMode>

                {
                    !dataAreLoading ?

                        <div className="daextam-admin-body">

                            <div className={'daextam-react-table'}>

                                <div className={'daextam-react-table-header'}>
                                    <div className={'statistics'}>
                                        <div className={'statistic-label'}>{__('All posts', 'daext-autolinks-manager')}:</div>
                                        <div className={'statistic-value'}>{statistics.allPosts}</div>
                                        <div className={'statistic-label'}>{__('Average Automatic Links', 'daext-autolinks-manager')}:</div>
                                        <div className={'statistic-value'}>{statistics.averageAl}</div>
                                    </div>
                                    <div className={'tools-actions'}>
                                        <button
                                            onClick={(event) => handleDataUpdateRequired(event)}
                                        ><img src={RefreshIcon} className={'button-icon'}></img>
                                            {__('Update metrics', 'daext-autolinks-manager')}
                                        </button>
                                    </div>
                                </div>

                                <div className={'daextam-react-table__daextam-filters daextam-react-table__daextam-filters-dashboard-menu'}>

                                    <div className={'daextam-search-container'}>
                                        <input
                                            onKeyUp={handleKeyUp}
                                            type={'text'} placeholder={__('Filter by title', 'daext-autolinks-manager')}
                                            value={formData.searchString}
                                            onChange={(event) => setFormData({
                                                ...formData,
                                                searchString: event.target.value
                                            })}
                                        />
                                        <input id={'daextam-search-button'} className={'daextam-btn daextam-btn-secondary'}
                                               type={'submit'} value={__('Search', 'daext-autolinks-manager')}
                                               onClick={() => setFormData({
                                                   ...formData,
                                                   searchStringChanged: formData.searchStringChanged ? false : true
                                               })}
                                        />
                                    </div>

                                </div>

                                <Table
                                    data={tableData}
                                    handleSortingChanges={handleSortingChanges}
                                    formData={formData}
                                />

                            </div>

                        </div>

                        :
                        <LoadingScreen
                            loadingDataMessage={__('Loading data...', 'daext-autolinks-manager')}
                            generatingDataMessage={__('Data is being generated. For large sites, this process may take several minutes. Please wait...', 'daext-autolinks-manager')}
                            dataUpdateRequired={dataUpdateRequired}/>
                }

            </React.StrictMode>

        </>

    );

};
export default App;