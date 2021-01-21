#include "Arduino.h"
#include "PMV.h"
#include <Math.h>

PMV::PMV(){
}
//t - temperatura powietrza [°C]
//t_r - œrednia temperatura promieniowania [°C]
//v - wzglêdna prêdkoœæ powietrza [m/s]
//rh - wilgotnoœæ wzglêdna [%]
//met - tempo metabolizmu [met]
//clo - izolacja odzie¿y [clo]
//wme  praca zewnêtrzna [met] domyœlnie 0
double PMV::calculatePMV(double t, double t_r, double v, double rh, double met, double clo, double w_met){
  
	double pa = rh * 10 * exp(16.6536 - 4030.183 / (t + 235));
	double icl = 0.155 * clo;
	double m = met * 58.15;
	double w = w_met * 58.15;
	double mw = m - w;
	double fcl;
	if (icl <= 0.078)
		fcl = 1 + (1.29 * icl);
	else
		fcl = 1.05 + (0.645 * icl);

	double hcf = 12.1 * sqrt(v);
	double hc = hcf;
	double taa = t + 273;
	double tra = t_r + 273;
	double tcla = taa + (35.5 - t) / (3.5 * icl + 0.1);

	double p1 = icl * fcl;
	double p2 = p1 * 3.96;
	double p3 = p1 * 100;
	double p4 = p1 * taa;
	double p5 = (308.7 - 0.028 * mw) + (p2 * pow((tra / 100.0), 4));
	float xn = tcla / 100;
	float xf = tcla / 50;
	double eps = 0.002;

	double n = 0;

	float hcn;
	while (abs(xn - xf) > eps)
	{
		xf = (xf + xn) / 2;
		hcn = 2.38 * pow(abs(100.0 * xf - taa), 0.25);
		if (hcf > hcn)
			hc = hcf;
		else
			hc = hcn;
		xn = (p5 + p4 * hc - p2 * pow(xf, 4)) / (100 + p3 * hc);
		n += 1;
		if (n > 150)
			break;
	}
	double tcl = 100 * xn - 273;

	double hl1 = 3.05 * 0.001 * (5733 - (6.99 * mw) - pa);
	double hl2;
	if (mw > 58.15)
		hl2 = 0.42 * (mw - 58.15);
	else
		hl2 = 0;

	double hl3 = 1.7 * 0.00001 * m * (5867 - pa);

	double hl4 = 0.0014 * m * (34 - t);

	double hl5 = 3.96 * fcl * (pow(xn, 4) - pow((tra / 100.0), 4));

	double hl6 = fcl * hc * (tcl - t);

	double ts = 0.303 * exp(-0.036 * m) + 0.028;
	double _pmv = ts * (mw - hl1 - hl2 - hl3 - hl4 - hl5 - hl6);

	return _pmv;
    }


double PMV::calculatePPD(double pmv) {
	double _ppd = 100.0 - 95.0 * exp(-0.03353 * pow(pmv, 4.0) - 0.2179 * pow(pmv, 2.0));
	return _ppd;
}
