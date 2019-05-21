import { COLORS } from '../constants/colors.js';

import { Pie } from 'react-chartjs-2';

const { Component } = wp.element;

/**
 * PieChart Component
 */
export class PieChart extends Component {

  constructor(props) {
    super(...arguments);
    this.props = props;

  }

  /**
   * When the component mounts it calls this function.
   */
  componentDidMount() {
    
  }
  /**
   * Renders the PieChart component.
   */
  render() {

    const data = leaderData.map(x => x.donationAmount);
    const names = leaderData.map(x => x.teamName);

    const config = {
            type: 'pie',
            data: {
                datasets: [{
                    data: data,
                    backgroundColor: COLORS,
                    label: 'Dataset 1'
                }],
                labels: names,
            },
            options: {
                responsive: true,
                legend: {
                    position: 'bottom',
                },
                title: {
                    display: false,
                    text: ''
                },
                animation: {
                    animateScale: true,
                    animateRotate: true
                }
            }
        };
   
    return (
       <Pie
          data={config.data}
          options={config.options}
        />
    );
  }

}


