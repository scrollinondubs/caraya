import React from 'react';

import { COLORS } from '../constants/colors.js';

import { Pie } from 'react-chartjs-2';

/**
 * PieChart Component
 */
class PieChart extends React.Component {

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
   
    const data = this.props.leaderData.map((x) => {
      return x.donationAmount === 0 ? 1 : x.donationAmount;
    });

    const names = this.props.leaderData.map(x => x.teamName);
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
                    display: true,
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
      <div id='pie-container'>
       <Pie
          data={config.data}
          options={config.options} />
      </div>
    );
  }

}


export default PieChart


