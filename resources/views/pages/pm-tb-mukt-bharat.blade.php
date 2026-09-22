@extends('layout.frontend')

@section('content')

<section class="page-header">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h1>Pradhan Mantri TB Mukt Bharat Abhiyan</h1>
                <ul class="breadcrumbs">
                    <li><a href="{{ url('/') }}">Home</a></li>
                    <li><img src="{{ asset('assets/images/double-arrow.svg') }}" alt=""></li>
                    <li>Pradhan Mantri TB Mukt Bharat Abhiyan</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<section class="pm-abhiyan-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="pm-abhiyan-content shadow-sm">
                    <h2>Introduction About Pradhan Mantri TB Mukt Bharat Abhiyan</h2>

                    <p>India has the world's highest tuberculosis (TB) burden, with an estimated 26 lakh people contracting the disease and approximately 4 lakh people dying from the disease every year. The economic burden of TB in terms of loss of lives, income and workdays is also substantial.</p>

                    <p>TB usually affects the most economically productive age group of society, resulting in a significant loss of working days and pushing TB patients further into the vortex of poverty.</p>

                    <p>The Ministry of Health and Family Welfare (MoHFW) is implementing an ambitious National Strategic Plan with the goal (SDG) to achieve End TB targets. The challenge of tuberculosis requires a multi-sectoral response to address the social determinants like nutritional support, living and working conditions, and an increase in access to diagnostic and treatment services.</p>

                    <p>Although the efforts of the government are yielding significant results, the community and the institutions in the society can play a critical role in filling gaps and addressing social determinants, thereby contributing to the national goal. For effective engagement of the community in the path towards ending TB in India, MoHFW is implementing the "Community Support to TB patients - Pradhan Mantri TB Mukt Bharat Abhiyaan".</p>

                    <p>Ni-kshay Mitra (Donor) for this program include co-operative societies, corporates, elected representatives, individuals, institutions, non-governmental organizations, political parties and partners who can support by adopting health facilities (for individual donor), blocks/urban wards/districts/states for accelerating response against TB to complement government efforts, as per the district-specific requirements in coordination with the district administration.</p>

                    <p>The State and district administration will support Ni-kshay Mitras in prioritizing districts and provide guidance on critical gap analysis and district-specific needs. The support provided to the patient under this initiative is in addition to the free diagnostics, free drugs and Ni-kshay Poshan Yojana provided by National TB Elimination Programme (NTEP) to all TB patients notified from both public and private sector.</p>

                    <h3>Objectives of the Initiative</h3>
                    <ol>
                        <li>Provide additional patient support to improve treatment outcomes of TB patients</li>
                        <li>Augment community involvement in meeting India's commitment to end TB.</li>
                        <li>Leverage Corporate Social Responsibility (CSR) activities</li>
                    </ol>

                    <h3>Expected Output of the Initiative</h3>
                    <ol>
                        <li>This initiative will increase the active involvement of society in the fight against tuberculosis.</li>
                        <li>This activity aims at increasing awareness among the public regarding tuberculosis.</li>
                        <li>Involvement of the community in supporting the treatment cascade shall also help in the reduction of stigma.</li>
                        <li>Provision of additional support to the TB patient shall also result in the reduction of the out-of-pocket expenditure for the family of the TB patient.</li>
                        <li>Ultimately improved nutrition for the TB patient shall result in better treatment outcomes.</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
