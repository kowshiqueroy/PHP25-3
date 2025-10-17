<?php
require_once 'head.php';
?>
<style>
    body {
        background-color: #f4f7fa;
        font-family: 'Segoe UI', sans-serif;
    }

    .card {
        background-color: #ffffff;
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        max-width: 900px;
        margin: 2rem auto;
    }

    .card h2 {
        font-size: 1.75rem;
        font-weight: 600;
        color: #2c3e50;
        margin-top: 1.5rem;
    }

    .card p {
        font-size: 1.1rem;
        color: #555;
        line-height: 1.6;
        margin-bottom: 1.5rem;
    }

    .card a {
        color: #0077cc;
        font-weight: 500;
        text-decoration: none;
    }

    .card a:hover {
        text-decoration: underline;
    }

    .team-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1.5rem;
        padding: 1rem 2rem;
        text-align: left;
    }

    .team-member {
        background-color: #f9fbfd;
        border-radius: 10px;
        padding: 1rem;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
    }

    .team-member h4 {
        margin-bottom: 0.25rem;
        font-size: 1.1rem;
        color: #2c3e50;
    }

    .team-member span {
        display: block;
        font-size: 0.95rem;
        color: #666;
    }

    @media (max-width: 768px) {
        .card {
            margin: 1rem;
            padding: 1rem;
        }

        .card h2 {
            font-size: 1.5rem;
        }

        .card p {
            font-size: 1rem;
        }
    }
</style>

<div class="card p-1 text-center">
  <!-- NGO Registration Section -->
<h2>Registration Details</h2>
<p>
    CivicThinkers is officially registered as a nonprofit organization in the United States.<br><br>
    <strong>Registration Number:</strong> 92-8471635<br>
    <strong>Registered Entity:</strong> CivicThinkers Foundation<br>
    <strong>Legal Status:</strong> 501(c)(3) Tax-Exempt Organization<br>
    <strong>Registered Office:</strong> 4351 Boardwalk Dr, Huntington Beach, CA 92649<br>
    <strong>Date of Incorporation:</strong> March 15, 2023<br>
    <strong>Jurisdiction:</strong> California Secretary of State
</p>

    <h2>Address</h2>
    <p>
        (714) 849-9336 <br>4351 Boardwalk Dr, Huntington Beach, CA 92649, USA
    </p>

    <h2>Contact Us</h2>
    <p>
        If you have any questions or would like to get in touch, please email us at<br>
        Help: <a href="mailto:civicthinkerss@gmail.com">civicthinkerss@gmail.com</a><br>
        Info: <a href="mailto:info@civicthinkers.com">info@civicthinkers.com</a><br>
        Jobs: <a href="mailto:jobs@civicthinkers.com">jobs@civicthinkers.com</a><br>
        or call us at (714) 849-9336.
    </p>

    <h2>About Us</h2>
    <p>
        CivicThinkers is a nonprofit organization headquartered in the United States, dedicated to fostering inclusive civic engagement and data-driven social impact. Our mission is to empower communities by amplifying their voices through thoughtful research, public opinion surveys, and participatory dialogue.
        <br><br>
        We believe that meaningful change begins with listening. By conducting comprehensive surveys, focus groups, and community outreach initiatives, we gather valuable insights into the needs, aspirations, and challenges faced by individuals across diverse backgrounds. These insights inform policymakers, educators, local leaders, and advocacy groups—helping shape programs and policies that reflect the real priorities of the people.
        <br><br>
        Our work spans a wide range of social issues, including education equity, public health, environmental sustainability, economic justice, and civic participation. Whether we're collaborating with grassroots organizations or advising municipal governments, our goal remains the same: to turn data into action and ensure that every voice counts.
        <br><br>
        As a values-driven NGO, CivicThinkers is committed to transparency, inclusivity, and ethical research practices. We strive to build bridges between communities and institutions, promote informed decision-making, and inspire a culture of active citizenship.
        <br><br>
        Together, we envision a world where public opinion is not just heard—but used as a catalyst for lasting, positive change.
    </p>

    <h2>Our Team</h2>
    <div class="team-grid">
        <div class="team-member">
            <h4>Dr. Maya Thompson</h4>
            <span>Executive Director</span>
            <span><a href="mailto:maya@civicthinkers.org">maya@civicthinkers.com</a></span>
        </div>
        <div class="team-member">
            <h4>Alex Rivera</h4>
            <span>Community Outreach Lead</span>
            <span><a href="mailto:alex@civicthinkers.org">alex@civicthinkers.com</a></span>
        </div>
        <div class="team-member">
            <h4>Priya Desai</h4>
            <span>Research & Data Analyst</span>
            <span><a href="mailto:priya@civicthinkers.org">priya@civicthinkers.com</a></span>
        </div>
        <div class="team-member">
            <h4>Jordan Lee</h4>
            <span>Policy & Advocacy Coordinator</span>
            <span><a href="mailto:jordan@civicthinkers.org">jordan@civicthinkers.com</a></span>
        </div>
    </div>

</div>

<?php
require_once 'foot.php';
?>